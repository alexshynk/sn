<?php
if (!isset($_SESSION)) session_start();

require_once "common.php";

if (isset($_SESSION["user_id"])){
	to_log("Користувач: {$_SESSION["user_login"]}, user_type={$_SESSION["user_type"]}, user_id = {$_SESSION["user_id"]}  вийшов з системи");
}
session_destroy();
?>