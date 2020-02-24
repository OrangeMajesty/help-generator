<?php

/*
УСТАНОВКА Libreoffice

yum install libreoffice-headless
yum install libreoffice-writer
yum install libreoffice-calc

shell_exec('/usr/bin/libreoffice6.3 --headless -convert-to pdf --outdir /home/bitrix/ext_www/portal.bitrix.center /home/bitrix/ext_www/portal.bitrix.center/testdoc.doc');
*/

$Str_data = [
	"Дмитрий мальков",
	"123",
	"321",
	"444",
	"hhhhh",
	"jjjjjjjj",
	"tttttt",
	"uuuuuuu",
];

include_once(__DIR__.'/class-editor.php');

if(!empty($_REQUEST['iin']) && !empty($_REQUEST['type']))
{
	$temp_folder = __DIR__."/history/".$_REQUEST['iin'];

	if (!file_exists($temp_folder)) 
	{
		mkdir($temp_folder, 0777, true);
		chmod($temp_folder, 0777);
	}

	// degig info
	// $application->DetailLog("Start app.");
	// $application->DetailLog($_REQUEST, true);
	// $application->DetailLog("Make dir for app.");
		


	// debug info
	// $application->DetailLog("Waiting for libreoffice shell to execute. Convert to odt.");
	// $application->DetailLog("Shell exec:".'/usr/bin/libreoffice6.3 --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/template.docx"),true);
	
	$ret_app = shell_exec(__DIR__.'\libreoffice\program\soffice.exe --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath(__DIR__."/template/".$_REQUEST['type'].".docx"));

	// $application->DetailLog("Returned answer from libreoffice: ". print_r($ret_app, true), true);
	//shell_exec('/usr/lib64/libreoffice --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/template.docx"));
	
	$odt = new OdtEditor(realpath($temp_folder."/".$_REQUEST['type'].".odt"));		
	// $application->DetailLog("Execut OdtEditor.");	
	$odt->NormalizePlaceholders();
	// $application->DetailLog("Execut OdtEditor completed.");	
	
	// $application->WriteLog("PLACEHOLDERS NORMALIZED");
			
	// 		$application->DetailLog("Replace strings. Info: The document does not work if the characters in the replacement are not valid.");	
	// 		$application->DetailLog($_REQUEST['properties']['Strings'], true);	


	//replace strings
	foreach($Str_data as $key=>$str)
	{
		// Документ не проходит если есть не подходящие знаки.
		$str = str_replace(array('&nbsp;','&', 'amp;'), array(' ', 'and', ''), $str);

		// $application->DetailLog("Replace \"".$str."\" String.");	

		//$odt->ReplaceText('*String'.($key+1).';*', $str);
		$odt->ReplaceStringNode(($key+1), $str);
		$odt->ReplaceTextInStyles('*String'.($key+1).';*', $str);
	}
			
	$i=1;
	while($i<100)
	{
		//$odt->ReplaceText('*String'.$i.';*', "");
		$odt->ReplaceStringNode($i, "");
		$odt->ReplaceTextInStyles('*String'.$i.';*', "");
		$i++;
	}
	// 		$application->DetailLog("Replace strings completed.");	
			
	// 		$application->DetailLog("Replace subfiles.");	
	//replace subfiles
	// $i=1;
	/*
	foreach($_REQUEST['properties']['Files'] as $pkey=>$docs) {
		$imgs = explode(',', $docs);
		foreach($imgs as $subkey => $img) {
			// $key = $pkey.$subkey;
			$key = $pkey;
		
			$fileid = intval($img);
			$application->DetailLog("Replace #".$key." subfile for id: ". $fileid);	
			
			$res=$application->restCommand('disk.file.get', array('id'=>$fileid));	

			if(!$res['result']['DOWNLOAD_URL']) $application->DetailLog("Error. Download url for subfile not valid. -> " . print_r($res, true));		

			if($res['result']['DOWNLOAD_URL'])
			{
				$application->DetailLog("Geted download url for subfile: ".print_r($res['result']['DOWNLOAD_URL']), true);

				$fileext = strrpos($res['result']['NAME'] , "." );
				$fileext = substr($res['result']['NAME'], $fileext);
				if($fileext!="" && strlen($fileext)<6)
				{
					$filename = $fileid."_".$subkey.$fileext;
					$application->WriteLog($filename);
					$application->DetailLog("Download file.");	
					file_put_contents($temp_folder.'/'.$filename, fopen($res['result']['DOWNLOAD_URL'], 'r'));
					
					switch($fileext)
					{
						default:
							//do nothing now..
						break;

						case '.xlsx':
						case '.xls':
							$application->DetailLog("Format file xlsx or xls.");

							$application->DetailLog("Waiting for libreoffice shell to execute. Convert to odt.");

							$application->DetailLog("Shell exec: ".'/usr/bin/libreoffice6.3 --headless -convert-to html --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$filename),true);
							$ret_app = shell_exec('/usr/bin/libreoffice6.3 --headless -convert-to html --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$filename));
							$application->DetailLog("Returned answer from libreoffice: ". print_r($ret_app, true), true);

							$application->DetailLog("Shell exec: ".'/usr/bin/libreoffice6.3 --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$fileid."_".$subkey.".html"),true);
							$ret_app = shell_exec('/usr/bin/libreoffice6.3 --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$fileid."_".$subkey.".html"));
							$application->DetailLog("Returned answer from libreoffice: ". print_r($ret_app, true), true);
							
							//shell_exec('/usr/lib64/libreoffice --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$filename));
							
							$subodt = new OdtEditor(realpath($temp_folder."/".$fileid."_".$subkey.".odt"));
							
							$odt->GetSubDocumentFiles($subodt, $temp_folder, $fileid."_".$subkey);
							
							//document body
							$body = $subodt->getODTBodyText();
							// $body .= '*File'.($key+1).';';								
							$body = str_replace("style:name=\"", "style:name=\"file".$fileid."_".$subkey."_", $body);
							$body = str_replace("style-name=\"", "style-name=\"file".$fileid."_".$subkey."_", $body);
							$body = str_replace("href=\"Pictures/", "href=\"Pictures/file".$fileid."_".$subkey."_", $body);						
							
							//document styles
							$styles = $subodt->getODTAutomaticStyles();
							$styles = str_replace("style-name=\"", "style-name=\"file".$fileid."_".$subkey."_", $styles);
							$styles = str_replace("style:name=\"", "style:name=\"file".$fileid."_".$subkey."_", $styles);
							$styles = str_replace("style:master-page-name=\"", "style:master-page-name=\"file".$fileid."_".$subkey."_", $styles);
							
							$application->DetailLog("The file is configured.");
							// $application->DetailLog($body);
							
							$odt->InsertBefore($styles, '</office:automatic-styles>');
							
							$application->WriteLog($body, "BODY");

							// $odt->ReplaceParagraphWithText('*File'.($key+1).';', '*File'.($key+1).';'.'*File'.($key+1).';');
							if(isset($imgs[$subkey+1]))
								$odt->AppendParagraphWithText('*File'.($key+1).';', '<text:p text:style-name="Standard">*File'.($key+1).';'.'*File'.($key+1).';</text:p>');
							$odt->ReplaceParagraphWithText('*File'.($key+1).';', $body);

							$subodt->close();
							break;

						case ".docx":
							$application->DetailLog("Format file docx.");

							$application->DetailLog("Waiting for libreoffice shell to execute. Convert to odt.");
							$application->DetailLog("Shell exec: ".'/usr/bin/libreoffice6.3 --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$filename),true);
							$ret_app = shell_exec('/usr/bin/libreoffice6.3 --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$filename));
							$application->DetailLog("Returned answer from libreoffice: ". print_r($ret_app, true), true);
							
							//shell_exec('/usr/lib64/libreoffice --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$filename));
							
							$filename = str_replace(".docx", ".odt", $filename);
							$subodt = new OdtEditor(realpath($temp_folder."/".$filename));
							
							$odt->GetSubDocumentFiles($subodt, $temp_folder, $fileid);
							
							//document body
							$body = $subodt->getODTBodyText();								
							$body = str_replace("style:name=\"", "style:name=\"file".$fileid."_", $body);
							$body = str_replace("style-name=\"", "style-name=\"file".$fileid."_", $body);
							$body = str_replace("href=\"Pictures/", "href=\"Pictures/file".$fileid."_", $body);						
							
							//document styles
							$styles = $subodt->getODTAutomaticStyles();
							$styles = str_replace("style-name=\"", "style-name=\"file".$fileid."_", $styles);
							$styles = str_replace("style:name=\"", "style:name=\"file".$fileid."_", $styles);
							$styles = str_replace("style:master-page-name=\"", "style:master-page-name=\"file".$fileid."_", $styles);
							
							$application->DetailLog("The file is configured.");
							
							$odt->InsertBefore($styles, '</office:automatic-styles>');

							if(isset($imgs[$subkey+1]))
								$odt->AppendParagraphWithText('*File'.($key+1).';', '<text:p text:style-name="Standard">*File'.($key+1).';'.'*File'.($key+1).';</text:p>');
							$odt->ReplaceParagraphWithText('*File'.($key+1).';', $body);
							
							$subodt->close();
						break;
						
						case ".jpeg":
						case ".jpg":
						case ".png":
						case ".gif":
							$application->DetailLog("Format file image.");
							if(isset($imgs[$subkey+1]))
								$odt->AppendParagraphWithText('*File'.($key+1).';', '<text:p text:style-name="Standard">*File'.($key+1).';'.'*File'.($key+1).';</text:p>');
							$odt->ReplaceContentParagraphWithImage('*File'.($key+1).';', $temp_folder, $filename, $fileid);
							$odt->ReplaceStylesParagraphWithImage('*File'.($key+1).';', $temp_folder, $filename, $fileid);
							$application->DetailLog("The file is configured.");
							
						break;
					}
				}
				else
				{
					$application->DetailLog("File extension has wrong value.");
					$application->WriteLog("FILE UPLOAD ERROR: File extension has wrong value");
				}
			}
			$i++;
		}
	}
	*/

	// 		$application->DetailLog("Replace subfiles completed.");	
			
	$i=1;
	while($i<100) {
		// $application->DetailLog("TEMP DEBUG INFO: memory usage ".memory_get_usage());	
		/*$odt->ReplaceParagraphWithText('*File'.$i.';', "");*/
		$odt->ClearContentFilePlaceholder('*File'.$i.';');
		// $application->DetailLog("TEMP DEBUG INFO: step two / i = ".$i);	
		$odt->ClearStylesFilePlaceholder('*File'.$i.';');
		$i++;
	}
	// 		$application->DetailLog("ODT session will be closed.");	
			$odt->close();

	// 		$application->DetailLog("Waiting for libreoffice shell to execute. Convert to pdf.");
	// 		$application->DetailLog("Shell exec: ".'/usr/bin/libreoffice6.3 --headless -convert-to pdf:writer_pdf_Export --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/template.odt"),true);
	// 		$ret_app = shell_exec('/usr/bin/libreoffice6.3 --headless -convert-to pdf:writer_pdf_Export --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/template.odt"));
	// 		$application->DetailLog("Returned answer from libreoffice: ". print_r($ret_app, true), true);

	shell_exec(__DIR__.'\libreoffice\program\soffice.exe --headless -convert-to pdf:writer_pdf_Export --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$_REQUEST['type'].".odt"));

			
	// 		//send result to Bitrix
	// 		$output_content = base64_encode(file_get_contents($temp_folder."/template.pdf"));
	// 		$application->DetailLog("Encode template.pdf to base64 completed.");
			
	// 		$output_filename = $_REQUEST['workflow_id'];
	// 		if($_REQUEST['properties']['TargetFilename']!="") $output_filename = $_REQUEST['properties']['TargetFilename'];

	// 		$application->WriteLog('TARGETFILE :' . $_REQUEST['properties']['TargetFilename']);
			
	// 		$application->DetailLog("Upload template.pdf to disk. folder id: ".$output_folder_id . ".");
	// 		$output_result = $application->restCommand('disk.folder.uploadfile', array('id'=>$output_folder_id, 'data'=>array('NAME'=>$output_filename.'.pdf'), 'fileContent'=>$output_content));

	// 		$output_result_id = $output_result['result']['ID'];
	// 		if(empty($output_result_id)) $application->DetailLog("Error. File template.pdf was not uploaded.");

	// 		$property_file = (string)((is_array($_REQUEST['properties']['FileOutput'])) ? $_REQUEST['properties']['FileOutput'][0] : $_REQUEST['properties']['FileOutput']);

	// 		if(empty($property_file)) $application->DetailLog("Warning! FileOutput not installed.");

	// 		if(!empty($property_file)) {

	// 				$application->DetailLog("Upload document to field type FILE.");

	// 				$application->DetailLog("Waiting get fields.");
	// 				$FIELDS = $application->restCommand('lists.element.get', array(
	// 					'IBLOCK_TYPE_ID' => 'bitrix_processes',
	// 					'IBLOCK_ID' => preg_replace('/[^\-\d]*(\-?\d*).*/','$1',$_REQUEST['document_type'][2]),
	// 					'ELEMENT_ID' => $_REQUEST['document_id'][2],
	// 				));
	// 				$application->DetailLog("Fields received");

	// 				$FIELDS_DATA = array();

	// 				foreach ($FIELDS['result'][0] as $key => $value) {
	// 					if(stristr($key, 'PROPERTY_') !== FALSE) {
	// 						$FIELDS_DATA[$key] = $application->ClearListElement($value);
	// 					} 
	// 				}

	// 				$application->WriteLog($FIELDS['result'][0]);
	// 				$FIELDS_DATA = array_merge($FIELDS_DATA, array(
	// 					'NAME' => $FIELDS['result'][0]['NAME'],
	// 					// PDT_TO_DOWNLOAD
	// 					'PROPERTY_'.$property_file => array(
	// 								urlencode($output_filename.'.pdf'),
	// 								$output_content
	// 						)
	// 					)
	// 				);

	// 				$application->DetailLog("Fields installed.");
	// 				$application->WriteLog($FIELDS_DATA);

	// 				$application->DetailLog("Pending field update.");
	// 				$FIELDS_UPDATE = $application->restCommand('lists.element.update', array(
	// 					'IBLOCK_TYPE_ID' => 'bitrix_processes', // lists
	// 					'IBLOCK_ID' => preg_replace('/[^\-\d]*(\-?\d*).*/','$1',$_REQUEST['document_type'][2]),
	// 					'ELEMENT_ID' => $_REQUEST['document_id'][2],
	// 					'FIELDS' => $FIELDS_DATA
	// 				));

	// 				$application->WriteLog($FIELDS_UPDATE);
	// 				$application->DetailLog("Fields: " . print_r($FIELDS, true), true);
	// 				$application->DetailLog("Document to field type FILE successfully uploaded.");
	// 			}

	// 		if($output_result_id>0) {
	// 			$return_values['OutputDocumentID'] = $output_result_id;

	// 		} else {
	// 			$application->DetailLog("Error. Can`t upload result file");
	// 			$application->DetailLog("Return values: ". print_r($return_values, true), true);

	// 			$application->WriteLog('ERROR:Can`t upload result file');
	// 			$application->WriteLog($output_result);
	// 			$application->ReturnActivityResult($_REQUEST['event_token'], "ERROR:Can`t upload result file", $return_values);
	// 		}


	// 	}
	// 	else
	// 	{
	// 		$application->DetailLog("Error. Can`t download template docx file with ID ".$template_fileid." located by '".$template_url."' url.");
	// 		$application->DetailLog("Return values: ". print_r($return_values, true), true);

	// 		$application->ReturnActivityResult($_REQUEST['event_token'], "ERROR:Can`t download template docx file with ID ".$template_fileid." located by '".$template_url."' url.", $return_values);
	// 	}

	// 	$application->ReturnActivityResult($_REQUEST['event_token'], "OK:Converted successfully", $return_values);
	// 	$application->delTree($temp_folder);
	// }
	// else
	// {
	// 	$application->ReturnActivityResult($_REQUEST['event_token'], "ERROR:Template file or output folder are not set", $return_values);
	// }

}
else
{
	// $application->ReturnActivityResult($_REQUEST['event_token'], "ERROR:BadRequest", $return_values);

}













