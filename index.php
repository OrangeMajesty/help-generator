<?php

function transliterateen($input){
$gost = array(
"a"=>"а","b"=>"б","v"=>"в","g"=>"г","d"=>"д","e"=>"е","yo"=>"ё",
"j"=>"ж","z"=>"з","i"=>"и","i"=>"й","k"=>"к",
"l"=>"л","m"=>"м","n"=>"н","o"=>"о","p"=>"п","r"=>"р","s"=>"с","t"=>"т",
"y"=>"у","f"=>"ф","h"=>"х","c"=>"ц",
"ch"=>"ч","sh"=>"ш","sh"=>"щ","i"=>"ы","e"=>"е","u"=>"у","ya"=>"я","A"=>"А","B"=>"Б",
"V"=>"В","G"=>"Г","D"=>"Д", "E"=>"Е","Yo"=>"Ё","J"=>"Ж","Z"=>"З","I"=>"И","I"=>"Й","K"=>"К","L"=>"Л","M"=>"М",
"N"=>"Н","O"=>"О","P"=>"П",
"R"=>"Р","S"=>"С","T"=>"Т","Y"=>"Ю","F"=>"Ф","H"=>"Х","C"=>"Ц","Ch"=>"Ч","Sh"=>"Ш",
"Sh"=>"Щ","I"=>"Ы","E"=>"Е", "U"=>"У","Ya"=>"Я","'"=>"ь","'"=>"Ь","''"=>"ъ","''"=>"Ъ","j"=>"ї","i"=>"и","g"=>"ґ",
"ye"=>"є","J"=>"Ї","I"=>"І",
"G"=>"Ґ","YE"=>"Є","_"=>" "
);
return strtr($input, $gost);
}

$pathdir = __DIR__.'/template/';
$button_list = '<span>Шаблон не обнаружено.</span>';

if(file_exists($pathdir))
{
	$dirlist = scandir($pathdir);

	if(count($dirlist) > 2)
		$button_list = "";

	foreach ($dirlist as $dir)
	{
		if($dir != "." && $dir != ".." && strpos($dir, ".docx") !== false)
		{
			$dirname = str_replace(".docx", "", $dir);
			$button_list .= '<a id="'.$dirname.'" href="#">'.transliterateen($dirname).'</a>';
			// var_dump($dir);
		}
	}

	// die(var_dump($button_list));
}

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
			<?php echo $button_list; ?>
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

jQuery(document).ready(function() {
	if(window.location.hash == "#invalid-iin")
		alert("Неверный иин");
});
	
</script>

</body>
</html>

