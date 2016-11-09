<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"/>
<link rel="stylesheet" type="text/css" href="../css/style.css"/>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<style>
/*input[id="friend_name"]{width: 30em;}*/
a{padding:0; margin:0;}
</style>
<title>Друзі</title>
<script type="text/javascript">
$(document).ready(function(){
	$("#err_msg").hide();
})

function fdel_friend(user_id, friend_user_id){
	try{
	$.ajax({
		method: "POST",
		url: "user_friends_del.php",
		data: {"user_id": user_id, "friend_user_id": friend_user_id},
		dataType: "json",
		async: false,
		success: function(data){
			if (data.err_code != 0){
				$("#err_msg").html((data.err_msg).replace(/\n/g,"<br>"));
				$("#err_msg").show(); 				
			}else location.reload(); 
		}
	});
	}
	catch(e){
		alert("Помилка:" + e.name + ":" + e.message + "\n" + e.stack);
	}
}
</script>
</head>
<body>
<?php include "top_banner.php"?>
<?php
require_once "error_handler.php";
require_once "common.php";
connect_to_db();
?>
<center><h3>Друзі</h3></center>
<div id="err_msg"></div>
<form method="post">
<div class="toll_bar">
<input type="button" value="Пошук нових друзів" onclick="location.href='face_search_friends.php'"/>
</div>
</form>

<?php
$query =sprintf(
"select 
	u.user_id,
	u.image_id, 
	u.user_type, 
	u.user_first_name, 
	u.user_surname,
	u.reg_date
from 
	users u 
	inner join link_usr_to_usr l on u.user_id = l.friend_user_id
where l.user_id = %d;",
$_SESSION["user_id"]
);

$result = mysql_query($query);
if (!$result) die("<p>Помилка:".mysql_error()."</p>");
echo "<table>";	
while($row = mysql_fetch_assoc($result)){
	echo <<<EOD
<tr>
<td>
<div class='user_box' style='width:500;'>
<table><tr>
<td>
<img src='get_img.php?image_id={$row["image_id"]}' style='width: 150; height: 150;'>
</td>
<td style='padding-left: 1em; position: relative;'>
<a href='face_user_info.php?user_id={$row["user_id"]}'> {$row["user_first_name"]} &nbsp&nbsp {$row["user_surname"]}</a><br>
Зареєстрований: {$row["reg_date"]}<br>
<input type='button' name='to_del_friend' value='Видалити з друзів' style='position: absolute; bottom:0px; margin:0; padding:0;' onclick='fdel_friend({$_SESSION["user_id"]},{$row["user_id"]})'/>
</td>
</tr></table>
</div>
</td>
</tr>
EOD;
	}
echo "</table>";
?>

</body>
</html>