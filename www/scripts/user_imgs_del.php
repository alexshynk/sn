<?php
header("Content-Type: application/json;");

require_once "ob_service.php";
ob_start("do_json_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	connect_to_db();
	mysql_query("START TRANSACTION");
	$image_id = isset($_GET["image_id"]) ? $_GET["image_id"] : -1;
	if (!($image_id>0)) throw new Exception("Помилка: не задано image_id");

	//перевірка, що користувач видаляє власне зображення
	$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1;
	$query = sprintf("select count(1) as count_ from link_img_to_usr where image_id=%d and user_id=%d",$image_id,$user_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: ".mysql_error());
	$row = mysql_fetch_row($result);
	if ($row[0]==0) throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");
	
	$query = sprintf("select filename from images where image_id = %d", $image_id);
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	
	$query = sprintf("delete from link_img_to_usr where image_id=%d;", $image_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка:".mysql_error());
	
	$query = sprintf("delete from images where image_id=%d;", $image_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка:".mysql_error());

	//видалити файл
	unlink("../img/{$row["filename"]}");
	
	mysql_query("COMMIT");
	
	$err_code = 0;
	$err_msg = "Зображення було успішно видалено";
}
catch(Exception $e){
	mysql_query("ROLLBACK");
	$err_code = -1;
	$err_msg = format_error($e);
	to_log($err_msg,1);
}

$log_echo = ob_get_contents();
ob_end_clean();
$err_msg = $log_echo.$err_msg;

echo json_encode(array("err_code" => $err_code, "err_msg" => $err_msg));
exit;
?>