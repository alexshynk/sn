<?php 
ob_start();
if (!isset($_SESSION["user_id"])) session_start();
ob_end_clean();

$user_login = isset($_SESSION["user_login"]) ? $_SESSION["user_login"] : "";

echo  <<<EOD
<div id="top_banner">
<input type="button"  value="на головну" onclick="location.href='../index.php'"/>
<span style="font-weight: bold; position: absolute; right: 25px; ">{$user_login}</span>
</div><br>
EOD;

if (!isset($_SESSION["user_id"]))
	die("<p style='color: red; font-size: large'>Ваша сесія завершена. Для продовження роботи необхідно зайти в систему</p>");

require_once "common.php";
check_user_state();
?>