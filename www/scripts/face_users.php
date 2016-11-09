<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8;"/>
<link rel="stylesheet" type="text/css" href="../css/style.css">
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<title>Користувачі</title>
<script type="text/javascript">
$(document).ready(function(){
	$("#err_msg").hide();
	
	//зняти всы флажки
	var checks = document.getElementsByName("usr_sel");
	for (i=0; i<checks.length; i++) checks[i].checked = false;	
});

function del_users(){
	var usr = document.getElementsByName("usr_sel");
		
	var count_ = 0;
	var ids = "";
	for(i=0; i<usr.length; i++) 
		if (usr[i].checked){
			count_ = count_ + 1;
			if (count_ == 1) ids=usr[i].value;
			else ids=ids+","+usr[i].value;
		}

	if (count_ > 0){
		if (confirm("Видалити користувачів")){
			try{
				$.ajax({
					method: "POST",
					url: "users_del.php",
					data: {"users" : ids},
					dataType: "json",
					success: function(data){
						if (data.err_code != 0){
							$("#err_msg").html((data.err_msg).replace(/\n/g,"<br>"));
							$("#err_msg").show(); 				
						}else location.reload(true); 
					}
				});
			}
			catch(e){
				alert('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
			}	   
		}
	}
}
</script>
</head>
<body>
<?php include "top_banner.php"?>
<?php require_once "error_handler.php";?>
<center><h3>Користувачі</h3></center>
<div id="err_msg"></div>
<form id="frm_search" method="get" action="">
<div class="toll_bar">
<input type="button" value="видалити" onclick="del_users();"/>
<div style="padding:0px; margin: 0px; position: absolute; right:20px; top:0;">
Введіть ім'я або логін користувача:&nbsp
<input type="text" name="search_str" value="<?php echo isset($_GET["search_str"]) ? trim($_GET["search_str"]) : "";?>"/> &nbsp&nbsp&nbsp
<input type="submit" value="Пошук"/>
</div>
</div>
<input type="hidden" name="search_flag" value="1"/>
</form>
<?php
if (!isset($_GET["search_flag"])) exit;

require_once "common.php";
connect_to_db();

$search_str = isset($_GET["search_str"]) ? trim($_GET["search_str"]) : "";
$query =sprintf(
"select * from users 
where user_state = 1 and user_id != %d
  and (upper(concat(user_first_name, user_surname)) like upper('%%%s%%')
	or user_login like upper('%%%s%%')
  )
order by user_type desc, user_first_name asc, user_surname asc;",
$_SESSION["user_id"], $search_str, $search_str); 
$result=mysql_query($query);
if (!$result) die("<p>Помилка: ".mysql_error()."</p>");

echo "<div style='height: 75%; overflow-y: auto; border: 2px solid green;';>";
echo "<table >";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	
//права користувача	
switch ($row["user_type"]){
	case 0: $user_rule = "користувач"; break;
	case 1: $user_rule = "<b>адміністратор</b>"; break;
	default : $user_rule = "";
}

//стать користувача
switch($row["user_sex"]){
	case "male": $user_sex= "чоловіча"; break;
	case "female":  $user_sex = "жіноча"; break;
	default: $user_sex = ""; 
}	
	
	
	echo "<tr>";
	echo "<td>";
	echo "<div class='user_box' style='width:550;'>";
	echo "<table><tr>";
	//-----------------------
	echo "<td style='width: 50px; vertical-align: middle; text-align: center;'>";
	echo "<input type='checkbox' name='usr_sel' value='{$row["user_id"]}' unchecked />";
	echo "</td>";
	//-----------------------
	echo "<td>";
	echo "<img src='get_img.php?image_id={$row["image_id"]}' style='width: 150; height: 150;'/>";
	echo "</td>";	
	//-----------------------
	echo "<td style='padding-left:1em;'>";
	echo "user_id: ".$row["user_id"]."<br>";
	echo "логін: ".$row["user_login"]."<br>";
	echo "права: {$user_rule}<br>";
	echo "ім'я фамілія: {$row["user_first_name"]} {$row["user_surname"]}<br>";
	echo "стать: {$user_sex}<br>";
	echo "email: {$row["user_email"]}<br>";
	echo "зареєстровано: {$row["reg_date"]}<br>";
	echo "</td>";
	//-----------------------
	echo "</tr></table>";
	echo "</div>";
	echo "</td>";
	echo "</tr>";	
}
echo "</table>";
echo "<div>";
?>
</body>
</html>