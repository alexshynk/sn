<?php
header('Content-Type: application/json;');

require_once "ob_service.php";
ob_start("do_json_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	connect_to_db();
	mysql_query("START TRANSACTION");
	if ((isset($_SESSION["user_type"]) ? $_SESSION["user_type"] : -1 )!= 1) 
		throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");
	
	$users = explode(",",isset($_POST["users"]) ? $_POST["users"] : "-1");
	foreach($users as $user_id){

	//видалити зображення користувача
	$query = sprintf(
		"select i.image_id, i.filename from images i inner join link_img_to_usr l on i.image_id = l.image_id".
		" where l.user_id = %d", $user_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка:".mysql_error());
	
	while($row = mysql_fetch_assoc($result)){
		
		$query = sprintf("delete from link_img_to_usr where image_id=%d;", $row["image_id"]);
		$result2 = mysql_query($query);
		if (!$result2) throw new Exception("Помилка:".mysql_error());
		
		$query = sprintf("delete from images where image_id=%d;", $row["image_id"]);
		$result2 = mysql_query($query);
		if (!$result2) throw new Exception("Помилка:".mysql_error());

		//видалити файл
		unlink("../img/{$row["filename"]}");	
	}
	
	//видалити аватар користувача
	
	
	//статус користувача встановити: 0 (не активний)
	$query = sprintf("update users set user_state = 0 where user_id = %d and user_id != %d;",$user_id, $_SESSION["user_id"]);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: ".mysql_error());	
	}
	$err_code = 0;
	$err_msg = "";
	
	mysql_query("COMMIT");
	
	foreach($users as $user_id) to_log("Користувач user_id={$user_id} був видалений");
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