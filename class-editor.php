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

		$nodes = $this->SearchNodesArraybyText($content, "*String", '*', ';*');
		foreach($nodes as $node)
		{
			$content = str_replace($node, strip_tags($node), $content);
		}
		$nodes = $this->SearchNodesArraybyText($content, "*File", '*', ';*');
		foreach($nodes as $node)
		{
			$content = str_replace($node, strip_tags($node), $content);
		}
		$this->addFromString(self::CONTENT_LOCATION, $content);
        $this->save();
		
		$content = $this->getFromName(self::STYLES_LOCATION);	
		$nodes = $this->SearchNodesArraybyText($content, "*String", '*', ';*');
		foreach($nodes as $node)
		{
			$content = str_replace($node, strip_tags($node), $content);
		}
		$nodes = $this->SearchNodesArraybyText($content, "*File", '*', ';*');
		foreach($nodes as $node)
		{
			$content = str_replace($node, strip_tags($node), $content);
		}
		$this->addFromString(self::STYLES_LOCATION, $content);
        $this->save();
	}
	
	public function ReplaceStringNode($key, $value)
    {
		$content = $this->getFromName(self::CONTENT_LOCATION);
		$node = $this->SearchNodebyText($content, '*String'.$key.';', '*String', ';*');
		$node = strip_tags($node);
		
		if($node!==false)
		{
			$node_settings= explode(";", $node);
			$full_node = $this->SearchNodebyText($content, '*String'.$key.';', '<text:p', '</text:p>');

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
	public function ClearFilePlaceholder($content, $placeholder)
	{
		/*$placeholder_pos = strpos($content, $placeholder);
		while($placeholder_pos!==false)
		{
			$tag_start = strrpos($content, "<text:", $placeholder_pos-strlen($content));
			$tag_end = strpos($content, "</text:", $placeholder_pos);
			if($tag_end!==false) $tag_end = strpos($content, ">", $tag_end);
			if($tag_end!==false) $tag_end = $tag_end+1;

			if($tag_start!==false && $tag_end!==false)
			{
				$tag = substr($content, $tag_start, $tag_end-$tag_start);
				$this->WriteLog($tag, "tag");
				$content = str_replace($tag, "", $content);
			}
			else
			{
				$content = str_replace($placeholder, "", $content);
			}

			$placeholder_pos = strpos($content, $placeholder);
		}
		
		return $content;*/

		$node = $this->SearchNodebyText($content, $placeholder, '*File', ';*');
		$node = strip_tags($node);
		
		if($node!==false)
		{
			$node_settings= explode(";", $node);
			$full_node = $this->SearchNodebyText($content, $placeholder, '<text:p', '</text:p>');

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
								if($setting[1]=="line")
								{
									$this->WriteLog("'".$full_node."' replaced with empty_string");
									$content = str_replace($full_node, '', $content);
								}								
							break;							
						}
					}
				}
				$content = str_replace($node, "", $content);
			}
		}
		return $content;		
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
	public function ReplaceParagraphWithImage($location, $placeholder, $temp_folder, $filename, $fileid)
	{	
		$content = $this->getFromName($location);
		$node = $this->SearchNodebyText($content, $placeholder, '*File', ';*');
		$node = strip_tags($node);
		
		if($node!==false)
		{
			$node_settings= explode(";", $node);
			/*$full_node = $this->SearchNodebyText($content, $placeholder, '<text:p', '</text:p>');*/
			$full_node = $this->SearchNodeInnerbyText($content, $placeholder, '<text:p', '</text:p>');
			// $this->WriteLog($full_node, "full_node");
			if($full_node!==false)
			{
				//put to Pictures
				$image_body = file_get_contents(realpath($temp_folder."/".$filename));
				
				$this->addFromString('Pictures/file'.$fileid.'_'.$filename, $image_body);
				$this->save();
				
				//put to manifest
				$fileext = strrpos($filename , "." );
				$fileext = substr($filename, $fileext);
				
				if($fileext!="" && strlen($fileext)<6)
				{
					$part="";
					switch($fileext)
					{
						case ".jpeg": 
						case ".jpg":
							$part='<manifest:file-entry manifest:full-path="Pictures/file'.$fileid.'_'.$filename.'" manifest:media-type="image/jpeg"/>';
						break;
						
						case ".png":
							$part='<manifest:file-entry manifest:full-path="Pictures/file'.$fileid.'_'.$filename.'" manifest:media-type="image/png"/>';
						break;
						
						case ".gif":
							$part='<manifest:file-entry manifest:full-path="Pictures/file'.$fileid.'_'.$filename.'" manifest:media-type="image/gif"/>';
						break;
						
					}

					$manifest_content = $this->getFromName(self::MANIFEST_LOCATION);
					$manifest_placeholder='</manifest:manifest>';
					$manifest_content = str_replace($manifest_placeholder, $part.$manifest_placeholder, $manifest_content);
					$this->addFromString(self::MANIFEST_LOCATION, $manifest_content);
					$this->save();
				}
				
				//put to content
				list($actual_width, $actual_height, $actual_type, $actual_attr) = getimagesize(realpath($temp_folder."/".$filename));
				$image_aspect_ratio = $actual_width/$actual_height;
				
				
				$image_width = 100;
				$image_height = 100;
				//$this->WriteLog($node_settings, "node_settings");
				//$this->WriteLog($image_aspect_ratio, "image_aspect_ratio");
				
				foreach($node_settings as $setting)
				{
					if($setting!="")
					{
						$setting = explode('=', $setting);
						switch($setting[0])
						{
							case "w": 
								if($setting[1]=="auto") $image_width=0;
								else $image_width = intval($setting[1]);
							break;
							
							case "h":
								if($setting[1]=="auto") $image_height=0;
								else $image_height = intval($setting[1]);
							break;
							
						}
					}
				}

				if($image_width==0 && $image_height>0) $image_width= $image_height*$image_aspect_ratio;
				if($image_height==0 && $image_width>0) $image_height= $image_width/$image_aspect_ratio;
								
				$image_width = number_format($image_width/36, 2, '.', '');			
				$image_height = number_format($image_height/36, 2, '.', '');
				
				//$this->WriteLog($image_width , "image_width");
				//$this->WriteLog($image_height , "image_height");
				
				/*$textblock = '<text:p text:style-name="file'.$fileid.'_Standard"><draw:frame draw:style-name="fr1" draw:name="Picture'.$fileid.'" text:anchor-type="as-char" svg:width="'.$image_width.'cm" svg:height="'.$image_height.'cm" draw:z-index="0"><draw:image xlink:href="Pictures/file'.$fileid.'_'.$filename.'" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame></text:p>';
				*/
				$textblock = '<draw:frame draw:style-name="fr1" draw:name="Picture'.$fileid.'" text:anchor-type="as-char" svg:width="'.$image_width.'cm" svg:height="'.$image_height.'cm" draw:z-index="0"><draw:image xlink:href="Pictures/file'.$fileid.'_'.$filename.'" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>';
				
				//$this->WriteLog($content , "image_content");
				//$this->WriteLog($full_node , "image_full_node");
				$content = str_replace($full_node, $textblock, $content);
				
				$this->addFromString($location, $content);
				$this->save();
			}
		}
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
	
	
	public function WriteLog($data, $title=false)
	{
		// file_put_contents(__DIR__ . '/debug/'.date("Y-m-d").'.log', "===".$title."======".date("H:i:s")."===".PHP_EOL, FILE_APPEND);
		// file_put_contents(__DIR__ . '/debug/'.date("Y-m-d").'.log', print_r($data,true).PHP_EOL, FILE_APPEND);
	}

}
?>
