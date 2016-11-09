<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"/>
<link rel="stylesheet"  type="text/css" href="../css/style.css"/>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<title>user's info</title>
<style>
a{padding: 0px; margin: 0px;}
img{width: 200px; height: 200px;}
</style>

<script type="text/javascript">
$(document).ready(function(){
	var tooltips = document.getElementsByClassName("tooltip");
	for(i=0; i<tooltips.length; i++){
		tooltips[i].addEventListener("mousemove", function(event){
			$(this).children("span").css({"left":event.offsetX+17, "top":event.offsetY + 17})
		});
	}
	
	$("#err_msg").hide();
});

function fsend_msg(user_id_from, user_id_to){
	var text = document.getElementById("user_msg").value;
	$.ajax({
		method: "GET",
		url: "send_msg_to_usr.php",
		data: {"user_id_from" : user_id_from, "user_id_to" : user_id_to, "text" : text},
		async: false,
		dataType: "json",
		success: function(data){
			if (data.err_code != 0){
				$("#err_msg").html(data.err_msg.replace(/\n/g,"<br>"));
				$("#err_msg").show();
			}
			else {
				$("#user_msg").html("");
				$("#err_msg").hide();
				location.reload(true);
			}
		}		
		
	});
}

</script>
</head>
<body>
<?php include "top_banner.php"?>
<?php require_once "error_handler.php";?>
<center><h3>
<?php
if (isset($_GET["only_msg"])) echo "Переписка з користувачем";
else echo "Персональна інформація користувача";
?>
</h3></center>
<div id="err_msg"></div>
<?php
require_once "common.php";
connect_to_db();

//отримуємо ідентифікатор користувача
$user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : -1;

$query = sprintf(
"select user_id, user_login, reg_date, image_id, user_type, user_first_name, user_surname, user_email from users where user_id=%d",
$user_id);
$result = mysql_query($query);
if (!$result) die("<p>Помилка: ".mysql_error()." в запиті<br>{$query}</p>");
if (mysql_affected_rows() == 0) die("<p>Інформація по користувачу відсутня</p>");

$row = mysql_fetch_assoc($result);

echo "<div class='user_box' style='width:500;';>";
echo "<table><tr>";
echo "<td >";
echo "<img src='get_img.php?image_id={$row["image_id"]}' style='width: 150; height: 150;'>";
echo "</td>";
echo "<td style='padding-left: 1em;'>";
echo "{$row["user_first_name"]} &nbsp&nbsp {$row["user_surname"]}<br>";
echo "зареєстрований: {$row["reg_date"]}<br>";
echo "</tr></table>";
echo "</div>";
echo "<hr></hr>";

echo "<table style='width: 100%; position: relative; border-collapse: separated; border-spacing: 10px 0px; table-layout: fixed;'>";
echo "<tr>";


if (!isset($_GET["only_msg"])){
echo "<td style='width: 50%; border: 2px dashed orange;'>";
echo "<div style='background-color: orange; text-align: center; padding: 3px; font-size: larger;'>Фотографії</div>";
$query = sprintf("select l.image_id, i.filename, i.image_description from link_img_to_usr l, images i where l.image_id = i.image_id and l.user_id=%d;",$user_id);
$result = mysql_query($query);
if (!$result) die("<p>Помилка при отриманні списку зображень користувача: ".mysql_error()."</p>");
echo "<center><table style='border-collapse: separated; border-spacing: 10 20px;'>";
$i=0;
$cols = 2;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$i++;
	if ($i % $cols == 1) echo "<tr>";	
	echo "<td '>";
	echo "<div class='tooltip'>";
	echo "<a href='../img/{$row["filename"]}'><img src='get_img.php?image_id={$row["image_id"]}'/> </a>";
	if (trim($row["image_description"]) != "")
	echo "<span class='hint'>{$row["image_description"]}</span>";
	echo "</div>";
	echo "</td>";
	if ($i % $cols == 0) echo "</tr>";
}
echo "</table></center>";
echo "</td>";
}
echo "<td style='width: 50%; border: 2px dashed orange;'>";
echo "<div style='background-color: orange; text-align: center; padding: 3px; font-size: larger;'>Текстові повідомлення</div>";
echo "<textarea id = 'user_msg' style='width: 98%; height: 7em; margin: 1%;'></textarea><br>";
echo "<input type='button' id='send_msg' style='margin-left: 1%;' value='відправити повідомлення' onclick='fsend_msg({$_SESSION["user_id"]},{$user_id})'/><br><br>";

$query = sprintf(
	" select '1' as sender, m.msg_id, concat(u.user_first_name,' ',u.user_surname) as from_, m.msg_date, m.msg_text".
	" from messages m inner join users u on m.user_id_from = u.user_id".
	" where m.user_id_from=%d and m.user_id_to = %d".
	" union".
	" select '2' as sender, m.msg_id, concat(u.user_first_name,' ',u.user_surname) as from_, m.msg_date, m.msg_text".
	" from messages m inner join users u on m.user_id_from = u.user_id".
	" where m.user_id_from=%d and m.user_id_to = %d".
	" order by msg_id desc",
	$_SESSION["user_id"], $user_id, $user_id, $_SESSION["user_id"]);
$result = mysql_query($query);
if (!$result) echo "<span style='color: red'>".mysql_error()."<br><br>{$query}</span>";

while($row=mysql_fetch_assoc($result)){
	if ($row["sender"] == "1"){
		echo "<span style='margin-left:10px;'>{$row["from_"]}</span>&nbsp&nbsp";
		echo "<span style='font-size: smaller; vertical-align: bottom;'>({$row["msg_date"]})</span><br>";
		echo "<div style='margin-left:10px; margin-right:20px; background-color: #82FA58; padding-left:5px; max-height: 7em; overflow-y: auto; word-wrap:break-word;'>";
		echo str_replace("\n","<br>",$row["msg_text"]);
		echo "</div><br>";
	}
	elseif ($row["sender"] == "2"){
		echo "<span style='margin-left:25px; font-size: medium;'>{$row["from_"]}</span>&nbsp&nbsp";
		echo "<span style='font-size: smaller; vertical-align: bottom;'>({$row["msg_date"]})</span><br>";
		echo "<div style='margin-left:25px; margin-right:5px; background-color: #F3F781; padding-left:5px; max-height: 7em; overflow-y: auto; word-wrap:break-word;' >";
		echo str_replace("\n","<br>",$row["msg_text"]);
		echo "</div><br>";
	}
}

echo "</td>";

echo "<tr>";
echo "<table>";

?>

</body>
</html>