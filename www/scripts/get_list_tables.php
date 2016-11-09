<?php
header("Content-Type: text/html; charset: UTF-8;");

require "ob_service.php";
ob_start("do_html_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	if ((isset($_SESSION["user_type"]) ? $_SESSION["user_type"] : -1 )!= 1) 
		throw new Exception("Запит відхилено - не відповідність параметрів сесії і переданих даних");
	
	$res = "";
	$dbname = isset($_GET["dbname"]) ? $_GET["dbname"] : "none";

	mysql_connect($_SESSION["dbhost"], $_SESSION["dbuser"], $_SESSION["dbpassw"])
		or die("<p>Error connecting to database: ".mysql_error()."</p>");

	mysql_select_db($dbname)
		or die("<p>Error connecting to database: ".mysql_error() . "</p>");

	$result = mysql_query("show tables;");
	$res=$res."<select  multiple style='width:100%; height: 100%;' ondblclick='ftable_query(this.options[this.selectedIndex].value)'>";
	while($row = mysql_fetch_row($result)){$res = $res."<option value='{$row[0]}'>{$row[0]}</option>\n";}
	$res=$res."</select>";
}
catch(exception $e){
	$err_msg = format_error($e);
	$res = do_html_response($err_msg);
	to_log($err_msg,1);
}
ob_end_clean();
echo str_replace("\n","<br>",$res);
?>