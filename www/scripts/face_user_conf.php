<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"/>
<link rel="stylesheet" type="text/css" href="../css/style.css"/>
<title>Налаштування</title>
<style>
input[type="text"]{width: 25em;}
span.validate_error{color: red;}	
</style>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="../js/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("#user_passw").validate({
		rules: {
			passw: {
				required: true,
				minlength: 5,
				maxlength: 15
			},
			passw_confirm: {
				equalTo: "#passw"
			}
		},
		messages: {
			passw: {
				required: "поле обов'язкове до введення",
				minlength: "до введення не менше 5 символів",
				maxlength: "до введення не більше 15 символів"
			},
			passw_confirm: {
				equalTo: "не співпадають паролі"
			}
		},
		errorPlacement: function(error, element){
			$("#"+element.attr("name")+"_validate_error").html(error);
		}
	});
	
	$("#user_conf").validate({
		rules: {
			fname: {
				required: true,
				maxlength: 30
			},
			lname: {
				required: true,
				maxlength: 30
			},
			email: {
				required: true,
				email: true
			}
		},
		messages: {
			fname: {
				required: "поле обов'язкове до введення",
				maxlength: "до введення не більше 30 символів"
			},
			lname: {
				required: "поле обов'язкове до введення",
				maxlength: "до введення не більше 30 символів"
			},
			email: {
				required: "поле обов'язкове до введення",
				email: "не корректно введено email"
			}			
		},
		errorPlacement: function(error, element){
			$("#"+element.attr("name")+"_validate_error").html(error);
		}
	});
	
	$("#err_msg").hide();
})

function enable_do_pers_data(){document.getElementById("do_pers_data").disabled=false;}

function fed_passw(user_id){
	if (!$("#user_passw").valid()) return false;

	var passw = document.getElementsByName("passw")[0].value;
	try{
		$.ajax({
			method: "GET",
			url: "user_conf_ed_passw.php",
			data: {"user_id": user_id, "passw": passw},
			async: false,
			dataType: "json",
			success: function(data){
				if (data.err_code != 0){
					$("#err_msg").html(data.err_msg.replace(/\n/g,"<br>"));
					$("#err_msg").show();
				}
				else {
					$("#err_msg").hide();
					location.reload(true);
				}
			}
		})
	} catch(e){
		alert("Помилка " + e.name + ": " + e.message + "\n" + e.stack);
	}
}

function fload_img(e){
	document.getElementById("loading_img").style.opacity = "1";
	e.preventDefault();
	$.ajax({
		method: "POST",
		url: "edit_avatar.php",
		data: new FormData(document.getElementById("user_img")),
		contentType: false,
		processData: false,
		cache: false,
		async: false,
		success: function(data){
				if (data.indexOf("err_code==0") ==-1){
					$("#err_msg").html((data).replace(/\n/g,"<br>"));
					$("#err_msg").show(); 	 
				}
				else location.reload(true);
		}
	});
	document.getElementById("loading_img").style.opacity = "0";
}

function fed_pers_data(user_id){
	if (!$("#user_conf").valid()) return false;
	
	var user_first_name = document.getElementsByName("fname")[0].value;
	var user_surname = document.getElementsByName("lname")[0].value;
	var user_email = document.getElementsByName("email")[0].value;

	var sex = document.getElementsByName("sex");
	for(i=0; i<sex.length; i++) if (sex[i].checked == true) var user_sex = sex[i].value;

	if (user_email.length == 0 || user_sex.length == 0){
		alert("Не заповнені всі обов'язкові для введення поля");
		exit;
	}
	
	try{
		$.ajax({
			method: "GET",
			url: "user_conf_ed_pers_data.php",
			data: {"user_id": user_id, "user_first_name": user_first_name, "user_surname": user_surname, "user_sex": user_sex, "user_email": user_email},
			async: false,
			dataType: "json",
			success: function(data){
				if (data.err_code != 0){
					$("#err_msg").html(data.err_msg.replace(/\n/g,"<br>"));
					$("#err_msg").show();
				}
				else {
					$("#err_msg").hide();
					location.reload(true);
				}
			}		
		});
	}
	catch(e){
		alert('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
	}
}
</script>
</head>
<body>
<?php include "top_banner.php"?>

<center><h3>Налаштування</h3></center>
<div id="err_msg"></div>
<?php
require_once "error_handler.php";
require_once "common.php";
echo "<br><br>";
connect_to_db();

//отримуємо інформацію по користувачу
$query = "select user_login, user_password, user_first_name, user_surname, user_sex, user_email, image_id from users where user_id='{$_SESSION["user_id"]}';";
$result=mysql_query($query);
if (!$result) die("<p>Помилка запиту інформації по користувачу:".mysql_error()."</p>");
if (mysql_num_rows($result) == 0) die("<p>Не вдалось знайти користувача з user_id={$_SESSION["user_id"]}</p>");

$row=mysql_fetch_array($result);

//права користувача
switch ($_SESSION["user_type"]){
	case 0: $user_rule = "користувач"; break;
	case 1: $user_rule = "адміністратор"; break;
	default : $user_rule = "";
}

//стать користувача
switch($row["user_sex"]){
	case "male": $male_value = "checked"; $female_value = ""; break;
	case "female":  $male_value = ""; $female_value = "checked"; break;
	default: $male_value = ""; $female_value = "";
}
?>

<form id="user_passw" action="">
<table style="border-spacing: 0px 5px;" >
<tr> 
<td style="width:170px;"><?php echo $user_rule;?>:</td><td style="width:0.5em;"></td>

<td style="width:auto;"><input type="text" name="login" readonly value = "<?php echo $row["user_login"];?>"/></td>
</tr>
<tr>
<td>пароль:</td><td>*</td>
<td><input type="password" id="passw" name="passw" value="<?php echo $row["user_password"]?>"  onkeypress="document.getElementById('do_passw').disabled=false"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="passw_validate_error"></span>
</td>
</tr>
<tr>
<td>підтвердження пароля:</td><td></td>
<td><input type="password" name="passw_confirm" value=""/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="passw_confirm_validate_error"></span>
</td>
</tr>
</table><br>
<input type="button" id="do_passw" value="Змінити пароль" disabled onclick="fed_passw(<?php echo $_SESSION["user_id"];?>)"/>
</form>
<hr></hr>


<form id="user_img" action="" method="post" enctype="multipart/form-data" accept-charset="utf-8" onsubmit="fload_img(event)">
<input type="hidden" name="MAX_FILE_SIZE" value="10500000"/>
<input type="hidden" name="image_id" value="<?php echo $row["image_id"]; ?>"/>
<input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION["user_id"]; ?>"/>
<img src="get_img.php?image_id=<?php echo $row["image_id"];?>" style="width: 150; height: 150;"/>&nbsp&nbsp
<input type="file" name="user_pic" accept="image/jpeg,image/png" onchange="document.getElementById('do_img').disabled=false;"/><br><br>
<input type="submit" id="do_img" value="Змінити аватар" disabled/>&nbsp&nbsp
<span id="loading_img" style="font-weight: bold; opacity: 0;">Завантаження ...</span>
</form>
<hr></hr>


<form id="user_conf" name="user_conf" action="" method="post">
Персональні дані<br>
<table>
<tr>
<td style="width:150">ім'я:</td><td style="width:0.5em;">*</td>
<td><input type="text"  name="fname" value="<?php echo $row["user_first_name"];?>" onkeypress="enable_do_pers_data()"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="fname_validate_error"></span>
</td>
</tr>
<tr> 
<td>фамілія:</td><td>*</td>
<td><input type="text"  name="lname" value="<?php echo $row["user_surname"];?>" onkeypress="enable_do_pers_data()"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="lname_validate_error"></span>
</td>
</tr>
<tr> 
<!-- onclick="javascript: return false;" - це readonly для radio -->
<td>стать:</td><td>*</td>
<td>чоловіча<input type="radio"  name="sex" value="male" <?php echo $male_value;?> onchange="enable_do_pers_data()"/>&nbsp&nbsp
    жіноча<input type="radio"  name="sex" value="female" <?php echo $female_value;?> onchange="enable_do_pers_data()"/>	
</td>
</tr>
<tr> 
<td>електронна адреса:</td><td>*</td>
<td><input type="text"  name="email" value="<?php echo $row["user_email"];?>" onkeypress="enable_do_pers_data()"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="email_validate_error"></span>
</td>
</tr>
</table><br>
<input type="button" id="do_pers_data" value="Редагувати персональні дані" onclick="fed_pers_data(<?php echo $_SESSION["user_id"];?>)" disabled/>
</form>
<hr></hr>

</body>
</html>