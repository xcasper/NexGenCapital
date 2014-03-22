<?php

//$injson = fopen('rows.json', 'r');
//$injson2 = json_decode($injson);

$injson = file_get_contents("rowsActual.json");
$injsonA = json_decode($injson, true);

//echo $injson.' -1- <br>';
print_r ($injsonA).' -2- <br>';

/*$removeBrackets = explode("], [", $injson);

//$numRows = count($removeBrackets);
foreach($removeBrackets as $key){
	list($arr['key1'][], $arr['key2'][], $arr['key3'][], $arr['key4'][]) = explode(",", $key);
}

//for($a = 0; $a < count($removeBrackets); $a++){
$a = 0;
foreach($arr as $display){
	$a++;
	echo $display['key1'][$a].'<br>';
	echo $display['key2'][$a].'<br>';
	echo $display['key3'][$a].'<br>';
	echo $display['key4'][$a].'<br>';
}*/

?>
