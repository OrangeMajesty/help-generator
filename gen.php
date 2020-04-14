<?php

/*
УСТАНОВКА Libreoffice

yum install libreoffice-headless
yum install libreoffice-writer
yum install libreoffice-calc

shell_exec('/usr/bin/libreoffice6.3 --headless -convert-to pdf --outdir /home/bitrix/ext_www/portal.bitrix.center /home/bitrix/ext_www/portal.bitrix.center/testdoc.doc');
*/

include_once(__DIR__.'/class-editor.php');
include_once(__DIR__.'/class-db.php');

if(!empty($_REQUEST['iin']) && !empty($_REQUEST['type']))
{
	$DB = new DB();
	$student = $DB->getStudent($_REQUEST['iin']);

	if(empty($student)) 
	{
		header('Location: /#invalid-iin');
		exit;
	}

	$month = [
	  'январь',
	  'февраль',
	  'март',
	  'апрель',
	  'май',
	  'июнь',
	  'июль',
	  'август',
	  'сентябрь',
	  'октябрь',
	  'ноябрь',
	  'декабрь'
	];
	$College = "Актюбинский Высший Политехнический Колледж";
	$SEI = "Алдияров Касымбек Тулеуович";

	if(strpos($_REQUEST['type'], "_kz") !== false)
	{
		$month = [
		  'Қаңтар',
		  'Ақпан',
		  'Наурыз',
		  'Сәуір',
		  'Мамыр',
		  'Маусым',
		  'Шілде',
		  'Тамыз',
		  'Қыркүйек',
		  'Қазан',
		  'Қараша',
		  'Желтоқсан'
		];
		$College = "АҚТӨБЕ ЖОҒАРЫ  ПОЛИТЕХНИКАЛЫҚ КОЛЛЕДЖІ";
		$SEI = "АЛДИЯРОВ ҚАСЫМБЕК ТӨЛЕУҰЛЫ";
	}	

	$date = getdate();
	$student[0]["Day"] = $date["mday"];
	$student[0]["Month"] = $month[date('n')-1];
	$student[0]["Year"] = $date["year"];
	$student[0]["Year"] = $date["year"];

	$student[0]["College"] = $College;
	$student[0]["SEI"] = $SEI;
	


	$local_temp_folred = "/history/".$_REQUEST['iin'];
	$temp_folder =  __DIR__.$local_temp_folred;

	if (!file_exists($temp_folder)) 
	{
		mkdir($temp_folder, 0777, true);
		chmod($temp_folder, 0777);
	}
	
	$ret_app = shell_exec(__DIR__.'\libreoffice\program\soffice.exe --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath(__DIR__."/template/".$_REQUEST['type'].".docx"));

	
	$odt = new OdtEditor(realpath($temp_folder."/".$_REQUEST['type'].".odt"));		
	$odt->NormalizePlaceholders();

	//replace strings
	foreach($student[0] as $key=>$str)
	{
		// Документ не проходит если есть не подходящие знаки.
		$str = str_replace(array('&nbsp;','&', 'amp;'), array(' ', 'and', ''), $str);

		$odt->ReplaceStringNode($key, $str);
		$odt->ReplaceTextInStyles('*_'.$key.';', $str);

		// $odt->ReplaceTextInStyles($key, "");
		// var_dump('*_'.$key.';*<br>');
	}

			
	
	$odt->close();
	// die();


	shell_exec(__DIR__.'\libreoffice\program\soffice.exe --headless -convert-to pdf:writer_pdf_Export --outdir '.realpath($temp_folder).' '.realpath($temp_folder."/".$_REQUEST['type'].".odt"));

	// chmod($temp_folder, 0755);

	$file = realpath($temp_folder."/".$_REQUEST['type'].".pdf");
	if(file_exists($file))
	{
		if(ob_get_level())
			ob_end_clean();

		header('Location: '.$local_temp_folred."/".$_REQUEST['type'].".pdf");
		exit;
	}

}

header('Location: /');
