<?php
header('Content-Type: text/html; charset=utf-8');

function format_echo($err_msg){return "?err_code=1&err_msg=".$err_msg;}
ob_start("format_echo");

require_once "error_handler.php";

if (!isset($_SESSION)) session_start();

require_once "common.php";
connect_to_db();

$usr_login = isset($_GET["usr_login"]) ? $_GET["usr_login"] : "";
$usr_passw = isset($_GET["usr_passw"]) ? $_GET["usr_passw"] : "" ;
try{
	$result=mysql_query("select user_id, user_type, user_login, user_password, user_first_name, user_surname, user_email from users where user_login='{$usr_login}' and user_password = '{$usr_passw}';");
	if (!$result) throw new Exception("Помилка: ".mysql_error());
	
	if (mysql_num_rows($result) == 0) throw new Exception("Не вірний логін чи пароль");
	
	$row=mysql_fetch_array($result);
	$_SESSION["user_id"] = $row["user_id"];
	$_SESSION["user_type"] = $row["user_type"];
	$_SESSION["user_login"] = $row["user_login"];
	$err_code = 0;
	$err_msg = "";
	to_log("Користувач: {$_SESSION["user_login"]}, user_type={$_SESSION["user_type"]}, user_id = {$_SESSION["user_id"]} увійшов у систему");
}
catch(exception $e){
	$err_code = 1;
	$err_msg = $e->getMessage();
	to_log($err_msg,1);
}
ob_end_clean();
echo "?err_code={$err_code}&err_msg={$err_msg}";
//header("location: ../index.php?err_code={$err_code}&err_msg={$err_msg}");
exit;
?>