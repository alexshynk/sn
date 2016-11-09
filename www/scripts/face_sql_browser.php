<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8 "/>
<link rel="stylesheet" type="text/css" href="../css/style.css"/>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<title>MySQL browser</title>
<style>
/*th{padding-left: 0px; padding-right: 0px;}*/
td(padding-left: 0px; padding-right: 0px;)
</style>

<script>
$(document).ready(function(){
	document.getElementById("dblist").selectedIndex="-1";	
});

//get list of tables in chosen database
function fget_list_tables(dbname){
	$.ajax({url: "get_list_tables.php",
			data: {"dbname":dbname},
			async: false,
			success: function(html){
			  $("#table_list").html(html);
			},
			dataType: "HTML"	
	});
	fmes_to_log("Обрано базу даних: "+db);
}

//run entered query
function frun_sql(){
	var dbname = document.getElementById("dblist").value;
	var query = document.getElementById("sql").value;
	
	$.ajax({
		method: "POST",
		url: "run_sql.php",
		data: {"dbname" : dbname, "query" : query},
		async: false,
		success: function(html){$("#table_res").html(html);}
	});
	
	if (query.length <= 100) fmes_to_log("Выконано запит до бази даних: '"+query+"'");
    else fmes_to_log("Выконано запит до бази даних: '"+query.substring(0,99)+"...");
}

//put message to log
function fmes_to_log(mes){
    function lpad2(val){while (val.toString().length < 2) val="0"+val; return val;}
	
    d = new Date();
	fd = lpad2(d.getHours())+":"+lpad2(d.getMinutes())+":"+lpad2(d.getSeconds())+" ";
	var log = document.getElementById("log");
	if (log.value.length==0) {log.value = fd+mes}
	else {	log.value = fd+mes +"\n"+ log.value;}
}

//query for chosen table
function ftable_query(table){
    document.getElementById("sql").value = "select * from "+table +";";	
	frun_sql();
}


</script>
</head>
<body>
<?php include "top_banner.php"?>
<center><h3>MySQL браузер</h3></center>
<textarea id="log" class="log" rows=4 readonly>
</textarea>

<?php
require_once "error_handler.php";
require_once "common.php";

$dbhost = isset($_POST["dbhost"]) ? $_POST["dbhost"] : DB_HOST;
$dbuser = isset($_POST["dbuser"]) ? $_POST["dbuser"] : DB_USER;
$dbpassw = isset($_POST["dbpassw"]) ? $_POST["dbpassw"] : "";
?>

<table style="width: 100%; border: solid 2px;">
<tr style="height: 480px;">
<td style="width: 30%; position: relative;">
<div style="width: 100%; height: 120px; position: relative;">
<?php
//якщо POST на підключення до БД
if (isset($_POST["to_connect"])){
		if(mysql_connect($dbhost, $dbuser, $dbpassw)){
			$_SESSION["dbhost"] = $dbhost;
			$_SESSION["dbuser"] = $dbuser;
			$_SESSION["dbpassw"] = $dbpassw;
			$_SESSION["admin_connection"] = 1;
			echo "<script>fmes_to_log('Підключення до БД успішно виконано');</script>";
			//fget_list_db();
		}
		else{
			unset($_SESSION["dbhost"]); unset($_SESSION["dbuser"]); unset($_SESSION["dbpassw"]); unset($_SESSION["admin_connection"]);
			echo "<script>fmes_to_log('connectin param: {$dbhost}, {$dbuser}, {$dbpassw}');</script>";
			echo "<script>fmes_to_log('Помилка підключення до БД: ". str_replace("'","",mysql_error())."');</script>";
		}
}
elseif(isset($_POST["to_disconnect"])){
	unset($_SESSION["dbhost"]); unset($_SESSION["dbuser"]); unset($_SESSION["dbpassw"]); unset($_SESSION["admin_connection"]);
}

//якщо підключення не встановлено
if (!isset($_SESSION["admin_connection"])){
  //вивести форму для вводу параматрів підключення
?>
<form method="post">
  <table>
  <tr>
  <td width="150">Хост бази даних:</td>
  <td><input type="text" name="dbhost" value="<?php echo $dbhost; ?>"/></td>
  </tr>
  
  <tr><td>Користувач:</td>
  <td><input type="text" name="dbuser" value="<?php echo $dbuser; ?>"/></td>
  </tr>
  
  <tr><td>Пароль:</td>
  <td><input type="password" name="dbpassw" value="<?php echo $dbpassw; ?>"/></td>
  </tr>
  </table><br>
  <input type="submit" name="to_connect" value="Підключитись до БД" style="position: absolute; bottom: 0;"/>
</form>
<!--   connect to db MySQL and choose database  -->
<?php
}
//якщо підключення встановлено
elseif (isset($_SESSION["admin_connection"])){
	if(!@mysql_connect($_SESSION["dbhost"], $_SESSION["dbuser"], $_SESSION["dbpassw"]))
	echo "<script>fmes_to_log('Помилка: ".mysql_error()."');</script>";
	
	
	//отримати перелік баз
	$db_list = "";
	$result = mysql_query("show databases;");
	while($row = mysql_fetch_row($result)) $db_list = $db_list."<option value='{$row[0]}'>{$row[0]}</option>\n";
	echo "<script>fmes_to_log('db_list={$db_list}');</script>";
?>
<form method="post">
  <table>
  <tr>
  <td width="150">Хост бази даних:</td>
  <td><input type="text" name="dbhost" value="<?php echo $_SESSION["dbhost"]; ?>" readonly /></td>
  </tr>
  
  <tr><td>Користувач:</td>
  <td><input type="text" name="dbuser" value="<?php echo $_SESSION["dbuser"]; ?>" readonly /></td>
  </tr>
  </table>
  <span style="color: green; font-weight: bold;">Підключення встановлено</span><br><br>
  <input type="submit" name="to_disconnect" value="Відключитись від БД" style="position: absolute; bottom: 0;"/>
</form>	
<?php
}
?>
</div><br>
Доступні бази даних: 
<select id="dblist" class="dbname" onchange="fget_list_tables(this.options[this.selectedIndex].value)" style="width : 170px;">
<?php echo isset($db_list) ? $db_list : "";?>
</select><br><br>

<!-- show tables -->
<div style="width:100%; position: absolute; bottom: 0;">
  Таблиці<br>
  <div id="table_list" style="width:100%; height: 200px; background-color: white; padding:0px; margin:0px;">
  </div>
  <!--select  multiple id="table_list" style="width:100%; height: 200px;" ondblclick="ftable_query(this.options[this.selectedIndex].value)">
  </select-->
</div>
</td>

<td style="position: relative;">
<div  style="height:200px; width: 100%">
  SQL запит
  <input type="button" value="Виконати" onclick="frun_sql()"/><br>
  <textarea id="sql"  cols=100 style="height: 100%; width: 100%; resize: none;"></textarea>
</div><br>


<div id="div_res"  style="width: 100%;  position: absolute; bottom: 0;">
  Результат SQL запиту
  <div  id="table_res" style="height: 200px; background-color: #FAFAFA; overflow-x: auto; position: relative;"></div>
</div>
</td>

</tr>
</table>


</body>
</html>