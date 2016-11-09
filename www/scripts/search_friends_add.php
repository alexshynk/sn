<?php
header('Content-Type: application/json;');

require_once "ob_service.php";
ob_start("do_json_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : -1;
	if (!($user_id > 0)) throw new Exception("Не вказано user_id користувача");
	
	if ((isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1) !=  $user_id)
		throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");	

	$friend_user_id = isset($_POST["friend_user_id"]) ? $_POST["friend_user_id"] : "";
	if (trim($friend_user_id) == "") throw new Exception("Не вказано friend_user_id");

	connect_to_db();
	$query = sprintf("insert into link_usr_to_usr values(%d, %d);",$user_id, $friend_user_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: ".mysql_error());
	
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