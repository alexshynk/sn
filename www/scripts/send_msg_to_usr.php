<?php
header("Content-Type: application/json;");

require_once "ob_service.php";
ob_start("do_json_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	connect_to_db();
	$user_id_from = isset($_GET["user_id_from"]) ? $_GET["user_id_from"] : "";
	if (!($user_id_from > 0)) throw new Exception("Помилка: не вказано відправника повідомлення");
	
	if ((isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1) !=  $user_id_from)
		throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");	

	$user_id_to = isset($_GET["user_id_to"]) ? $_GET["user_id_to"] : "";
	if (!($user_id_to > 0)) throw new Exception("Помилка: не вказано отримувача повідомлення");
	
	$text = isset($_GET["text"]) ? $_GET["text"] : "" ;
	if (trim($text) == "") throw new Exception("Помилка: спроба відправити пусте повідомлення");
	$text = mysql_real_escape_string($text);
	
	$query = sprintf("insert into messages(user_id_from, user_id_to, msg_text) values(%d, %d, '%s');",
					$user_id_from, $user_id_to, $text);
	$result = mysql_query($query);
	if(!$result) throw new Exception("Помилка: ".mysql_error());
	
	$err_code = 0;
	$err_msg = "";
}
catch(Exception $e){
	$err_code = -1;
	$err_msg = format_error($e);
	to_log($err_msg, 1);
}

$log_echo = ob_get_contents();
ob_end_clean();
$err_msg = $log_echo.$err_msg;

echo json_encode(array("err_code" => $err_code, "err_msg" => $err_msg));
exit; 
?>

