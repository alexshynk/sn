<?php
header("Content-Type: text/html;charset=utf-8;");

require "ob_service.php";
ob_start("do_html_response");

require_once "error_handler.php";
require_once "common.php";
session_start();
try{
	if ((isset($_SESSION["user_type"]) ? $_SESSION["user_type"] : -1 )!= 1) 
		throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");
	
	$res = "";
	$dbname = isset($_POST["dbname"]) ? $_POST["dbname"] : "none";
	$query = isset($_POST["query"]) ? $_POST["query"] : "";
	
	if(!@mysql_connect($_SESSION["dbhost"], $_SESSION["dbuser"], $_SESSION["dbpassw"]))
	throw new Exception("Помилка: не вдалось підключитись до MySQL сервера \n".mysql_error());
  
	if(!@mysql_select_db($dbname))
	throw new Exception("Помилка: не вдалось вибрати базу даних \n".mysql_error());

	if (!@mysql_query("SET names 'utf8'"))
		throw new Exception("Помилка встановлення кодуваняя ".mysql_error());	
    
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: SQL запит не був виконаний \n".mysql_error());

	if (preg_match("/^\s*(INSERT|UPDATE|DELETE)/i",$query)){
		$numrows = mysql_affected_rows();
		$res = $res .  "<textarea readonly style='background-color: #D8F6CE; height: 99%; width: 100%;'> ".$numrows." rows were affected</textarea>";
	}
	elseif(preg_match("/^\s*(CREATE|DROP)/i",$query)){
		$res = $res .  "<textarea readonly style='background-color: #D8F6CE; height: 99%; width: 100%;'> DDL instruction was performed</textarea>";
	}
	else{   
		$field_count = mysql_num_fields($result);

		$cwidth = 170; //стандартна ширина стовпця
		$twidth =  0;//ширина таблицы
		$head = "";
		for($i=0; $i<$field_count; $i++){
			$field_types[$i] = mysql_field_type($result, $i);
			if ($field_types[$i]== "blob"){$cwidth_[$i] = $cwidth*3; $type = "<span style='color: blue;'>[BLOB 250+]</span>"; }
			else {$cwidth_[$i] = $cwidth; $type = "";}
			$head = $head."<td  style='border: solid 1px; width: {$cwidth_[$i]}px; text-align: center; font-weight: bold;'>".mysql_field_name($result,$i)."{$type}</td>";
			$twidth = $twidth + $cwidth_[$i];
		}		
		
		//заголовки
		$res = $res .  "<div style='height: 20px; width: auto;  position: absolute;'>\n";
		$res = $res .  "<table style='background-color: #E6E6E6; table-layout: fixed; border-collapse: collapse; width: {$twidth}px;'>\n";
		$res = $res . "<tr  style='height: 16px; font-size: 14px;'>\n";
		$res = $res . $head;
		$res = $res . "</tr>\n";
		$res = $res .  "</table>\n";
		$res = $res .  "</div><br>\n";	

		//дані
		$res = $res .  "<div style='height: 160px;  overflow-y: auto; overflow-x: hidden; position: absolute;'>\n";
		$res = $res .  "<table style='background-color: #F2F5A9; table-layout: fixed; border-collapse: collapse; width: {$twidth}px;'>\n";		
		while($row = mysql_fetch_array($result,MYSQL_NUM)){	 
			$res = $res . "<tr  style='height: 16px; font-size: 14px;'>\n";
			foreach($row as $key => $val){ 
				//if ($field_types[$key]== "blob") $val = "<a href='show_blob.php?data=".urlencode($val)."' target='_blank'>BLOB</a>";
				if ($field_types[$key]== "blob"){
					$val = substr($val,0,250);
				}
				$res = $res . "<td style='border: solid 1px; width: {$cwidth_[$key]}px; word-wrap:break-word;'>{$val}</td>";
			}
			$res = $res . "</tr>\n";
		}
		$res = $res .  "</table></div>";
	}
}
catch(Exception $e){
	$err_msg = format_error($e);
	$res = do_html_response($err_msg);
	to_log($err_msg,1);
}
ob_end_clean();
echo $res;
?>