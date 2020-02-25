<?php

//var_dump(__DIR__.'\libreoffice\program\soffice.exe');
// var_dump(shell_exec(__DIR__.'\libreoffice\program\soffice.exe --headless -convert-to pdf --outdir '.__DIR__.' '.__DIR__.'/mp.docx'));
// $ret_app = shell_exec(__DIR__.'\libreoffice\program\soffice.exe --headless -convert-to odt:"writer8" --outdir '.realpath($temp_folder).' '.realpath(__DIR__."/template/".$_REQUEST['type'].".docx"));
?>
<!DOCTYPE html>
<html>
<head>
	<title>Генератор справок</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="/assets/style.css">
</head>
<body>
<div class="container">
	<div class="logo">

		<img src="/assets/white_logo.png" alt="Logo type" width="233" height="219">
	</div>
	<div class="form-generator">
		<h1>
			Генератор документов
		</h1>
		<span>Введите иин</span>
		<input type="text" id="iin">
		<div class="button-list">
			<a id="a_kz" href="#">Получить справку о Чем то</a>
			<a id="a_kz" href="#">Получить справку о Чем то</a>
			<a id="a_kz" href="#">Получить справку о Чем то</a>
			<a id="a_kz" href="#">Получить справку о Чем то</a>
		</div>
	</div>
</div>
<script
	src="https://code.jquery.com/jquery-3.4.1.min.js"
	integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	crossorigin="anonymous">
</script>
<script>
jQuery("#iin").on("input", function(){
	var list = jQuery(".button-list a");
	var iin = jQuery("#iin").val();
	list.each(function(index, el) {
		console.log(index, el.id);
		el.href = "/gen.php?type="+el.id+"&iin="+iin;
	});
	
});
	
</script>

</body>
</html>

