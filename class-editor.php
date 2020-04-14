<?php
date_default_timezone_set('Etc/GMT+3');
session_start();

class OdtEditor extends \ZipArchive
{

    const CONTENT_LOCATION = 'content.xml';
	const STYLES_LOCATION = 'styles.xml';
	const MANIFEST_LOCATION = 'META-INF/manifest.xml';
    /**
     * @var string
     */
    protected $path;

    /**
     * ODT constructor.
	*/
    public function __construct($path)
    {
        $this->path = $path;
		$result = $this->open($path);
		if($result!==true)
		{
			// $this->WriteLog("OdtEditor ERROR: Unable to open ".$path." .Result is ".$result);
		}
		if($this->numFiles==0)
		{
			// $this->WriteLog("OdtEditor ERROR: Archive not exists or is empty.");
		}
    }
	
	public function save()
    {
        $this->close();
        $this->open($this->path, \ZipArchive::CREATE);
    }
	
	public function NormalizePlaceholders()
	{
		$content = $this->getFromName(self::CONTENT_LOCATION);	
		
		// $this->WriteLog($content, "content");

		$nodes = $this->SearchNodesArraybyText($content, "*_", '*', ';*');

		if (is_array($nodes) || is_object($nodes))
			foreach($nodes as $node)
				$content = str_replace($node, strip_tags($node), $content);

		$this->addFromString(self::CONTENT_LOCATION, $content);
        $this->save();
		
		$content = $this->getFromName(self::STYLES_LOCATION);	
		$nodes = $this->SearchNodesArraybyText($content, "*_", '*', ';*');

		if (is_array($nodes) || is_object($nodes))
			foreach($nodes as $node)
				$content = str_replace($node, strip_tags($node), $content);

		
		$this->addFromString(self::STYLES_LOCATION, $content);
        $this->save();
	}
	
	public function ReplaceStringNode($key, $value)
    {
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$node = $this->SearchNodebyText($content, '*_'.$key.';', '*', ';*');
		$node = strip_tags($node);
		
		if($node!==false)
		{
			$node_settings= explode(";", $node);
			$full_node = $this->SearchNodebyText($content, '*_'.$key.';', '<text:p', '</text:p>');

			if($full_node!==false)
			{
				foreach($node_settings as $setting)
				{
					if($setting!="")
					{
						$setting = explode('=', $setting);
						switch($setting[0])
						{
							case "del": 
								if($setting[1]=="line" && $value=="")
								{
									// $this->WriteLog("'".$full_node."' replaced with empty_string");
									//$this->WriteLog($content, "BEFORE");
									//$this->WriteLog(str_replace($full_node, "", $content), "AFTER");
									$content = str_replace($full_node, '', $content);
								}								
							break;							
						}
					}
				}
				$content = str_replace($node, $value, $content);
				// $this->WriteLog("'".$node."' replaced with '".$value."'");	
				
				$this->addFromString(self::CONTENT_LOCATION, $content);
				$this->save();
			}
		}
    }
	
	public function ReplaceText($from, $to)
    {
        $content = $this->getFromName(self::CONTENT_LOCATION);		
        $content = str_replace($from, $to, $content);	
        $this->addFromString(self::CONTENT_LOCATION, $content);
        $this->save();
    }
	
	public function ReplaceTextInStyles($from, $to)
    {
        $content = $this->getFromName(self::STYLES_LOCATION );		
        $content = str_replace($from, $to, $content);	
        $this->addFromString(self::STYLES_LOCATION , $content);
        $this->save();
    }
	
	public function getODTBodyText()
	{
		$content = $this->GetNode($this->getFromName(self::CONTENT_LOCATION), '<office:text', '</office:text>');		
		return $content;
	}
	
	public function getODTAutomaticStyles()
	{
		$content = $this->GetNode($this->getFromName(self::CONTENT_LOCATION), '<office:automatic-styles', '</office:automatic-styles>');		
		return $content;
	}
	
	public function GetNode($content, $nodestart, $nodeend)
	{
		$result = false;
		
		
		$pos = strpos($content, $nodestart);
		if($pos!==false)
		{
			$pos = strpos($content, ">", $pos)+1;
			$node = substr($content, $pos);
			$pos = strpos($node, $nodeend);
			if($pos!==false)
			{
				$node = substr($node, 0, $pos);
				return $node;
			}
			
		}
		
		return $result;
		
	}
	
	public function SearchNodebyText($content, $text, $nodestart, $nodeend)
	{
		$result = false;
		
		$text_pos = strpos($content, $text);
		if($text_pos!==false)
		{
			$start = strrpos($content, $nodestart, $text_pos-strlen($content));
			if($start!==false)
			{
				$end = strpos($content, $nodeend, $text_pos);
				if($end!==false)
				{
					$node = substr($content, $start, $end-$start+strlen($nodeend));
					if($node!="") return $node;
				}
				
			}	
		}		
		return $result;
	}
	
	public function SearchNodeInnerbyText($content, $text, $nodestart, $nodeend)
	{
		$result = false;
		
		$text_pos = strpos($content, $text);
		if($text_pos!==false)
		{
			$start = strrpos($content, $nodestart, $text_pos-strlen($content));
			if($start!==false) $start = strpos($content, ">", $start)+1;
			if($start!==false)
			{
				$end = strpos($content, $nodeend, $text_pos);
				if($end!==false)
				{
					$node = substr($content, $start, $end-$start);
					if($node!="") return $node;
				}	
			}	
		}		
		return $result;
	}
	
	public function SearchNodesArraybyText($content, $text, $nodestart, $nodeend)
	{
		$result = false;
		
		$text_pos = strpos($content, $text);
		while($text_pos!==false)
		{
			$start = strrpos($content, $nodestart, $text_pos-strlen($content));
			if($start!==false)
			{
				$end = strpos($content, $nodeend, $text_pos);
				if($end!==false)
				{
					$node = substr($content, $start, $end-$start+strlen($nodeend));
					if($node!="") $result[]=$node;
				}
				
			}
			$text_pos = strpos($content, $text, $text_pos+1);			
		}		
		return $result;
	}
	
	public function ClearContentStringPlaceholder($placeholder)
	{
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$content=$this->ClearFilePlaceholder($content, $placeholder);
		$this->addFromString(self::CONTENT_LOCATION, $content);
		$this->save();	
	}
	
	public function ClearContentFilePlaceholder($placeholder)
	{
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$content=$this->ClearFilePlaceholder($content, $placeholder);
		$this->addFromString(self::CONTENT_LOCATION, $content);
		$this->save();	
	}
	public function ClearStylesFilePlaceholder($placeholder)
	{
		$content = $this->getFromName(self::STYLES_LOCATION);
		$content=$this->ClearFilePlaceholder($content, $placeholder);
		$this->addFromString(self::STYLES_LOCATION, $content);
		$this->save();	
	}	
	public function ReplaceParagraphWithText($placeholder, $text)
	{
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$node = $this->SearchNodebyText($content, $placeholder, '<text:p', '</text:p>');
		if($node!==false)
		{
			//$this->WriteLog("'".$node."' replaced with '".$text."'");	
			$content = str_replace($node, $text, $content);
			$this->addFromString(self::CONTENT_LOCATION, $content);
			$this->save();
		}
	}
	public function AppendParagraphWithText($placeholder, $text)
	{
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$node = $this->SearchNodebyText($content, $placeholder, '<text:p', '</text:p>');
		if($node!==false)
		{
			//$this->WriteLog("'".$node."' replaced with '".$text."'");	
			$pos = strpos($content, $node);
			$content = substr($content, 0,$pos) . $text . substr($content, $pos);
			// $content = str_replace($node, $text, $content);
			$this->addFromString(self::CONTENT_LOCATION, $content);
			$this->save();
		}
	}
	public function ReplaceContentParagraphWithImage($placeholder, $temp_folder, $filename, $fileid)
	{
		$this->ReplaceParagraphWithImage(self::CONTENT_LOCATION, $placeholder, $temp_folder, $filename, $fileid);
	}
	public function ReplaceStylesParagraphWithImage($placeholder, $temp_folder, $filename, $fileid)
	{
		
		$this->ReplaceParagraphWithImage(self::STYLES_LOCATION, $placeholder, $temp_folder, $filename, $fileid);
	}
	
	public function GetSubDocumentFiles($subodt, $temp_folder, $fileid)
	{
		$subodt->extractTo($temp_folder."/".$fileid);
		$images = scandir(realpath($temp_folder."/".$fileid."/Pictures/"));
		foreach($images as $image)
		{
			if(strlen($image)>3)
			{
				$image_body = file_get_contents(realpath($temp_folder."/".$fileid."/Pictures/".$image));
				$this->addFromString('Pictures/file'.$fileid.'_'.$image, $image_body);
				$this->save();
			}
		}
		//update manifest for document
		$submanifest = file_get_contents(realpath($temp_folder."/".$fileid."/META-INF/manifest.xml"));
		$submanifest = str_replace("path=\"Pictures/","path=\"Pictures/file".$fileid.'_', $submanifest);
		
		$current_pos=0;
		while (($pos = strpos($submanifest, "path=\"Pictures/", $current_pos))!==false)
		{
			$current_pos=$pos+1;
			$start = strrpos($submanifest, "<manifest", $pos-strlen($submanifest));
			$end = strpos($submanifest, "/>", $pos);
			$part = substr($submanifest, $start, $end-$start+3);
			
			$manifest_content = $this->getFromName(self::MANIFEST_LOCATION);
			$manifest_placeholder='</manifest:manifest>';
			$manifest_content = str_replace($manifest_placeholder, $part.$manifest_placeholder, $manifest_content);
			$this->addFromString(self::MANIFEST_LOCATION, $manifest_content);
			$this->save();
		}
	}

	public function InsertBefore($text, $placeholder)
	{
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$content = str_replace($placeholder, $text.$placeholder, $content);
		$this->addFromString(self::CONTENT_LOCATION, $content);
		$this->save();		
	}
	
}
?>
