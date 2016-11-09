<?php
header("Content-Type: application/json;");

require_once "ob_service.php";
ob_start("do_json_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	connect_to_db();	
	$error_code = 0;	
	$image_id = isset($_GET["image_id"]) ? $_GET["image_id"] : -1;
	if (!($image_id>0)) throw new Exception("Помилка: не задано image_id");
	
	//перевірка, що користувач редагує власне зображення
	$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1;
	$query = sprintf("select count(1) as count_ from link_img_to_usr where image_id=%d and user_id=%d",$image_id,$user_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: ".mysql_error());
	$row = mysql_fetch_row($result);
	if ($row[0]==0) throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");
	
	$desc = isset($_GET["desc"]) ? $_GET["desc"] : "";
	if (trim($desc) != "") $desc = "'{$desc}'"; else $desc = "null";

	$query = sprintf("update images set image_description =%s where image_id = %d",$desc, $image_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: ".mysql_error());
	
	$err_code = 0;
	$err_msg = "";
}
catch(Exception $e){
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