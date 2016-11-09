<?php
ob_start();
require_once "error_handler.php";

$image_id = isset($_GET["image_id"]) ? $_GET["image_id"] : "";
if (!($image_id > 0)) exit;
try{
	require_once "common.php";
	connect_to_db();

	$query = sprintf("select * from images where image_id = %d;", $image_id);
	$result = mysql_query($query);
	if (!$result) die("<p>Помилка: ".mysql_error()."</p>");
	if (mysql_num_rows($result) == 0) die("<p>Відсутнє зображення з image_id: {$image_id}</p>");

	$image = mysql_fetch_assoc($result);
	
	ob_end_clean();
	header('Content-type: ' . $image['mime_type']);
	header('Content-length: ' . $image['file_size']);
	echo $image["image_data"];
	exit;
}
catch(exception $e) {
	$err_msg = $e->getMessage();
	to_log($err_msg,1);
	ob_end_clean();
}
?>