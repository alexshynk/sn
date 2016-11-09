<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"/>
<link rel="stylesheet" type="text/css" href="../css/style.css"/>
<title>Повідомлення</title>
<style>
a{padding:0; margin:0;}
</style>
</head>
<body>
<?php include "top_banner.php"?>

<center><h3>Повідомлення</h3></center>

<?php
require_once "error_handler.php";
require_once "common.php";
connect_to_db();

$query = sprintf("
select
	lm.user_id,
	lm.user_id_2,
	lm.image_id_2,
	lm.user_name_2,
	lm.reg_date_2,
	m.msg_date,
	m.msg_text,
	concat(u.user_first_name,' ',u.user_surname) as from_,
	case when u.user_id = lm.user_id then 1 else 2 end as sender
from v_last_user_messages lm
	inner join messages m on lm.msg_id = m.msg_id 
	inner join users u on u.user_id = m.user_id_from
where lm.user_id = %d;"
,$_SESSION["user_id"]);
$result = mysql_query($query);
if (!$result) echo "Помилка: ".mysql_error();

echo "<table style='table-layout: fixed; width: 100%;'>";
while ($row = mysql_fetch_assoc($result)){
switch($row["sender"]){
	case 1: $color = "#82FA58"; break;
	case 2: $color = "#F3F781"; break;
}
	
echo <<<EOD
<tr>

<td style="width: 40%; border-bottom: 2px solid white;">
<div class="user_box" style="margin-left: 5px; margin-right: 5px;">
<table><tr>
<td>
<img src='get_img.php?image_id={$row["image_id_2"]}' style='width: 75; height: 75;'>
</td>
<td style='padding-left: 1em; position: relative;'>
{$row["user_name_2"]}<br>
Зареєстрований: {$row["reg_date_2"]}<br>
<input type="button" value="Перейти до переписки" style="position: absolute; bottom: 0px;" onclick="location.href='face_user_info.php?user_id={$row["user_id_2"]}&only_msg=1'"/>
</td>
</tr></table>
</div>
</td>

<td style="width: 60%; border-bottom: 2px solid white; ">
<span style='margin-left:25px; font-size: medium;'>{$row["from_"]}</span>&nbsp&nbsp
<span style='font-size: smaller; vertical-align: bottom;'>({$row["msg_date"]})</span><br>
<div style='margin-left:25px; margin-right:5px; background-color: {$color}; padding-left:5px; height: 4em; overflow-y: auto; word-wrap:break-word;'>
{$row["msg_text"]}
</div>
</td>

</tr>


EOD;
}
echo "</table>";

?>


</body>
</html>