<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
<title>Соціальна мережа</title>
<style>
div.usr_sign {position: absolute; right: 0; top: 0;}
table{padding:0;}
td{padding:0; margin:0;}
</style>
  
<script type="text/javascript">
//увійти в систему
function fsign_in(){
	var usr_login = document.getElementById("usr_login").value;
	var usr_passw = document.getElementById("usr_passw").value;
	
	var pub = "";
	try {
		$.ajaxSetup({async: false});
		$.get("scripts/sign_in.php",
			{"usr_login": usr_login,  "usr_passw": usr_passw},
			function(data){pub = data;}
			);
	} catch(e) {
		alert('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
	}
	location.href = location.pathname + pub;
	//location.href = location.pathname + "?err_code=1&err_msg=test";
}

function fsign_out(){
	$.ajax({
		url: "scripts/sign_out.php",
		async: false
	});
	location.reload();
}
</script>
  
</head>
<!--body style="background: url(img/main.jpg) no-repeat; background-size: 100%; padding: 0px;"-->
<body >
<div class="opacity80" style="background: url(img/main.jpg) no-repeat; background-size: 100%; padding: 0px;">
</div>
<?php
require_once "scripts/error_handler.php";

session_start();
require_once "scripts/common.php";
save_user_visit();

connect_to_db();

//якщо користувач ще не зайшов у систему
if (!isset($_SESSION["user_id"])){
	if ((isset($_GET["err_code"]) ? $_GET["err_code"] : 0) > 0)	$err_msg = isset($_GET["err_msg"]) ? $_GET["err_msg"] : "";
	else $err_msg = "";	
	
	echo <<<EOD
<div class="usr_sign user_box" style="width: 300px;">
<table>
<tr>
<td style="width: 90px;">користувач</td>
<td ><input type="text" id="usr_login" name="user" style="width: 150px;" value=""/></td>
</tr>
<tr>
<td>пароль</td>
<td><input type="password" id="usr_passw" name="passwd" style="width: 150px;"/></td>
</tr>
<tr><td>&nbsp</td></tr>
<tr>
<td style="text-align: left;"><input type="button" name="sign_in" value="увійти" onclick="fsign_in()"/></td>
<td style="text-align: right;"><input type="button" name="to_reg" value="зареєструватись" onclick="location.href='scripts/face_reg_user.php'"/></td>
</tr>
</table>
<p style="color: red;">$err_msg</p>
</div>
<br>
<div style="padding:10px; z-index:10; position: absolute; top:0; bottom:0;">
<b>Соціальна мережа os7866</b><br><br>
<a href="html/about.html" style="margin:0; padding:0;">Про сайт</a>
</div>
EOD;
}

//якщо користувач зайшов у систему
else{	
	//отримуємо інформацію по користувачу
	$query = "select user_login, user_first_name, user_surname, user_email, image_id from users where user_id='{$_SESSION["user_id"]}';";
	$result=mysql_query($query);
	if (!$result) die("<p>Помилка запиту інформації по користувачу:".mysql_error()."</p>");
	if (mysql_num_rows($result) == 0){
		require_once "scripts/sign_out.php";
		die("<p>Ваша сесія завершена. Для продовження роботи необхідно зайти в систему.</p>");
	}
	$row=mysql_fetch_array($result);
	$user_login = $row["user_login"];
	$user_first_name = $row["user_first_name"];
	$user_surname = $row["user_surname"];
	$user_email = $row["user_email"];
	$image_id = $row["image_id"];
	
	check_user_state(false);
	echo <<<EOD
<div class="usr_sign user_box" style="width: 450; ">
<table>
<tr>
<td>
<img src="scripts/get_img.php?image_id=$image_id" style="width: 150; height: 150;"/>
</td>
<td>
<p>&nbsp логін: $user_login</p>
<p>&nbsp користувач: $user_first_name $user_surname</p>
<p>&nbsp email: $user_email</p>
&nbsp <input type="button" name="sign_out" value="вийти" onclick="fsign_out()"/>
</td>
</tr>
</table>
</div>
<div style="padding:10px; z-index:10; position: absolute; top:0; bottom:0;">
EOD;
	echo "<ul id='ul1'>";
	if ($_SESSION["user_type"] == 1)
		echo <<<EOD
***<br>
<a href="scripts/info.php">Параметри PHP</a>
<a href="scripts/face_sql_browser.php">MySQL браузер</a>
<a href="scripts/face_users.php">Користувачі</a>
<br><br>
EOD;

	echo <<<EOD
***<br>	
<a href="scripts/face_user_imgs.php">Фото</a>
<a href="scripts/face_user_friends.php">Друзі</a>
<a href="scripts/face_search_friends.php">Пошук друзів</a>
<a href="scripts/face_user_messages.php">Повідомлення</a>
<a href="scripts/face_user_conf.php">Налаштування</a>
EOD;
	echo "</ul>";
	echo "<script src='js/link_in_list.js'></script>";
}
?>
</div>

<!--span style="font-size: x-large; color: red; margin: 50px; display: block; position: absolute; bottom: 0px;">
!Сайт на стадії розробки та тестування.<br>
Персональні дані користувачів можуть бути видалені під час чергового оновлення сайту без попередження.
</span-->

<div class="footer">
  developed by Oleksandr Shynkaruk, &nbsp email: shurik.shynk@gmail.com
</div>
</body>
</html>