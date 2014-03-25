JSON Test Starting...

<?
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("memory_limit", "768M" );

$json_data = file_get_contents('rows.json');
json_decode($json_data, true, 1);
print_r($json_data);
?>

JSON Test Finished...