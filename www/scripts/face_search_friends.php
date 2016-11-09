<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"/>
<link rel="stylesheet" type="text/css" href="../css/style.css"/>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<style>
input[id="friend_name"]{width: 30em;}
a{padding: 0; margin: 0;}
</style>
<title>Друзі</title>
<script type="text/javascript">
$(document).ready(function(){
	$("#err_msg").hide();
})

function fadd_friend(user_id, friend_user_id){
	try{
	$.ajax({
		method: "POST",
		url: "search_friends_add.php",
		data: {"user_id": user_id, "friend_user_id": friend_user_id},
		dataType: "json",
		async: false,
		success: function(data){
			if (data.err_code != 0){
				$("#err_msg").html((data.err_msg).replace(/\n/g,"<br>"));
				$("#err_msg").show(); 				
			}else $("#frm_search").submit();
			//location.reload(); 
		}
	});
	}
	catch(e){
		alert("Помилка:"+e.name + ":" + e.message + "\n" + e.stack);
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
<center><h3>Пошук нових друзів</h3></center>
<div id="err_msg"></div>
<form id="frm_search" method="get" action="">
<!--form method="post" action="user_friends.php"-->
<div class="toll_bar">
Введіть ім'я друга:&nbsp&nbsp&nbsp
<input type="text" name="search_str" value="<?php echo isset($_GET["search_str"]) ? trim($_GET["search_str"]) : "";?>"/> &nbsp&nbsp&nbsp
<input type="hidden" name="search_flag" value="1"/>
<input type="submit" value="Пошук"/>
</div>
</form>

<?php
//якщо пошук нових друзів
if (isset($_GET["search_flag"])){
$search_str = isset($_GET["search_str"]) ? trim($_GET["search_str"]) : "";

	$query =sprintf(
"select 
	u.user_id,
	l.friend_user_id, 
	u.image_id, 
	u.user_type, 
	u.user_first_name, 
	u.user_surname,
	u.reg_date
from 
	users u 
	left join link_usr_to_usr l on u.user_id = l.friend_user_id and l.user_id= %d
where l.user_id is null
  and u.user_state = 1
  and u.user_id != %d 
  and upper(concat(u.user_first_name, u.user_surname)) like upper('%%%s%%');",
$_SESSION["user_id"], $_SESSION["user_id"], $search_str
);	

echo "<br><br><br>";
$result = mysql_query($query);
if (!$result) die("Помилка:".mysql_error()." в запиті <br>{$query}");
	
if (mysql_affected_rows() == 0) die("<h3>Нажаль по вашому запиту нічого не знайдено</h3>");
	
echo "<table>";	
while($row = mysql_fetch_assoc($result)){
	echo  <<<EOD
<tr>
<td>
<div class='user_box' style='width:500;';>
<table><tr>
<td>
<img src='get_img.php?image_id={$row["image_id"]}' style='width: 150; height: 150;'>
</td>
<td style='padding: 1em; position: relative;'>
<a href='face_user_info.php?user_id={$row["user_id"]}'> {$row["user_first_name"]} &nbsp&nbsp {$row["user_surname"]}</a><br>
Зареєстрований: {$row["reg_date"]}<br>
<input type='button' name='to_add_friend' value='Додати в друзі' style='position: absolute; bottom:0px;' onclick='fadd_friend({$_SESSION["user_id"]},{$row["user_id"]})'/>
</tr></table>
</div>
</td>
</tr>
EOD;
	}
echo "</table>";
}
?>

</body>
</html>