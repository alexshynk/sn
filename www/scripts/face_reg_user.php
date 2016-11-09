<!-- Форма реєстрації нового користувача -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8;">
<link rel="stylesheet" type="text/css" href="../css/style.css"/>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="../js/jquery.validate.min.js"></script>
<title>Реєстрація користувачів</title>
<style>
span.validate_error{color: red;}
</style>

<!-- validate the register form -->
<script type="text/javascript">
$(document).ready(function(){
	$("#reg_form").validate({
		
		rules: {
			login: {
				required: true,
				minlength: 5,
				maxlength: 30
			},
			passw: {
				required: true,
				minlength: 5,
				maxlength: 15
			},
			passw_confirm: {
				equalTo: "#passw"
			},
			fname: {
				required: true,
				maxlength: 30
			},
			lname: {
				required: true,
				maxlength: 30
			},
			sex: {
				required: true
			},
			email: {
				required: true,
				email: true
			}
			
		},
		messages: {
			login: {
				required: "поле обов'язкове до введення",
				minlength: "до введення не менше 5 символів",
				maxlength: "до введення не більше 30 символів"
			},
			passw: {
				required: "поле обов'язкове до введення",
				minlength: "до введення не менше 5 символів",
				maxlength: "до введення не більше 15 символів"
			},
			passw_confirm: {
				equalTo: "не співпадають паролі"
			},
			fname: {
				required: "поле обов'язкове до введення",
				maxlength: "до введення не більше 30 символів"
			},
			lname: {
				required: "поле обов'язкове до введення",
				maxlength: "до введення не більше 30 символів"
			},
			sex: {
				required: "поле обов'язкове до введення"
			},
			email: {
				required: "поле обов'язкове до введення",
				email: "не корректно введено email"
			}			
		},
		errorPlacement: function(error, element){
			$("#"+element.attr("name")+"_validate_error").html(error);
		}

	})
})
</script>

</head>
<body>
<?php require_once "top_banner_2.php";?>
<?php
require_once "error_handler.php";
require_once "common.php";
require_once "class_img.php";

//if(send_email($msg, "shurik_shink@mail.ru", "анониму", "Test mail", "Проверка отправки почты\r\n строка1 \r\n строка2") != 0) echo $msg;

//якро була натиснута кнопка "зареєструватись"
if (isset($_POST["to_reg"])) {
  connect_to_db();
  try{
	mysql_query("START TRANSACTION");
	
	$query = sprintf("select count(1) as count_ from users where user_login='%s';", $_POST["login"]);
	$result=mysql_query($query);
	if (!$result) throw new Exception("Помилка перевірки на повторну реестрацію: ".mysql_error());
	
	$row = mysql_fetch_array($result);
	if ($row["count_"] > 0) throw new Exception("Користувач з логіном '".$_POST["login"]."' вже зареестрований в системі");

  
	//*****************************************************//
	//  1. вставляємо аватар якщо було вибране зображення  //
	//*****************************************************//
	
	// потенційні помилки при передачі файлу
	$php_errors = array(0 => 'File is correct',
						1 => 'Maximum file size in php.ini exceeded',
						2 => 'Maximum file size in HTML form exceeded',
						3 => 'Only part of the file was uploaded',
						4 => 'No file was selected to upload.');  
					  
	// помилки при передачі файлу
	$error_code=isset($_FILES["user_pic"]["error"]) ? $_FILES["user_pic"]["error"]: -1;
	$error_msg= isset($php_errors[$error_code]) ? $php_errors[$error_code] : "undefined error code: ".$error_code;
	if ($error_code!=0 && $error_code!=4) throw new Exception(isset($php_errors[$error_code]) ? $php_errors[$error_code] : "undefined error code: ".$error_code);
	
	if ($error_code==0){
		//перевіряємо, що файл був завантаженый за допомогою HTTP POST
		if (!@is_uploaded_file($_FILES["user_pic"]["tmp_name"])) throw new Exception("Помилка: аватар був завантаженый не через HTTP POST");
	
		$new_img = new ex_img_ico;
		$new_img->initialise_img($_FILES["user_pic"]["tmp_name"], $_FILES["user_pic"]["type"]);
		$new_img->convert_img();
	
		$query = "insert into images(mime_type, file_size, image_data) ".
		"values('{$_FILES["user_pic"]["type"]}', {$new_img->ico_size}, '".$new_img->ico_data."');";
	
		$result = mysql_query($query);
		if (!$result) throw new Exception("Помилка при збереженні аватара: ".mysql_error());
		
		$image_id = mysql_insert_id();
	}
	else $image_id = 'null';
  
	//*****************************************//
	//     2. вставляємо користувача           //
	//*****************************************//
	$query = sprintf("insert into users(user_type, user_login, user_password, user_first_name, user_surname, user_sex, user_email, image_id)".
					 "values(%d, '%s', '%s', '%s', '%s', '%s', '%s', %s);",
				     0, $_POST["login"], $_POST["passw"], $_POST["fname"], $_POST["lname"], $_POST["sex"], $_POST["email"], $image_id);
	$result=mysql_query($query);
	if (!$result) throw new Exception("Помилка реєстрації користувача: ".mysql_error());
	
	
	//***************************************************************//
	//     3. зчитуємо дані по зареєстрованому користувачу           //
	//***************************************************************//	
	$query = sprintf("select user_id, user_login, user_password, user_email, user_first_name, user_surname from users where user_id = %d;", mysql_insert_id());
	$result=mysql_query($query);
	if (!$result) throw new Exception("Помилка отримання даних користувача: ".mysql_error());
	if (mysql_num_rows($result) == 0) throw new Exception("Помилка: користувач не був зареєстрований через невідому помилку.");
		
	$row=mysql_fetch_array($result);
	
	$to = $row["user_email"];
	$to_name = $row["user_first_name"]." ".$row["user_surname"];
	$subject = "Дякуємо за реєстрацію, {$_SERVER["HTTP_HOST"]}";
	$body = "Вітаемо  {$row["user_first_name"]} {$row["user_surname"]}, ви вдало зареєструвались на сайті http://{$_SERVER["HTTP_HOST"]}";
	$body = $body."\r\n логін: {$row["user_login"]} \r\n пароль: {$row["user_password"]}";
	if (send_email($err_msg, $to, $to_name, $subject, $body ) != 0){
		echo "<span style='font-size: large; color: red;'>{$err_msg}</span>";
	}
	
	echo "<p>Вітаемо {$row["user_login"]} ({$row["user_first_name"]} {$row["user_surname"]})</p>" ;
	echo "<p>Ви вдало зареєструвались</p><br><br>";
	echo "<input type=\"button\" value=\"перейти на головну сторінку\" onclick=\"location.href='../'\"/>";
	to_log("Зареєстровано нового користувача: user_id={$row["user_id"]}, user_login={$row["user_login"]} ({$row["user_first_name"]} {$row["user_surname"]})");
	
	mysql_query("COMMIT");
	
	exit; //якщо немає помилок завершуємо роботу скрипта (і формування WEB сторінки)
  }
  catch(Exception $e){
	mysql_query("ROLLBACK");
	$error_msg = $e->getMessage();
	echo "<div id='err_msg'>Помилка: ".str_replace("\n","<br>",$error_msg)."</div>";
  }
} //if (isset($_POST["to_reg"]))

?>

<center><h3>Реєстраційна форма</h3></center>
<form id="reg_form" name="reg_form" action="" method="post" enctype="multipart/form-data" >
<input type="hidden" name="MAX_FILE_SIZE" value="10500000"/>
<table style="border-spacing: 0px 10px;" >
<tr> 
<td style="width:150">логін:</td>
<td style="width:auto">
<span style="color: red;">*</span><input type="text"  name="login" value="<?php echo isset($_POST["login"]) ? $_POST["login"] : "" ;?>"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="login_validate_error"></span>
</td>
</tr>
<tr>
<td>пароль:</td>
<td><span style="color: red;">*</span><input type="password" id="passw"  name="passw"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="passw_validate_error"></span>
</td>
</tr>
<td>підтвердження пароля:</td>
<td><span style="color: red;">*</span><input type="password"  name="passw_confirm"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="passw_confirm_validate_error"></span>
</td>
</tr>
<td>аватар (JPG, PNG)</td>
<td>&nbsp&nbsp<input type="file" name="user_pic" accept="image/jpeg,image/png" size="30"/></td>
</tr>
<tr> 
<td>ім'я:</td>
<td><span style="color: red;">*</span><input type="text"  name="fname" value="<?php echo isset($_POST["fname"]) ? $_POST["fname"] : "";?>"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="fname_validate_error"></span>
</td>
</tr>
<tr> 
<td>фамілія:</td>
<td><span style="color: red;">*</span><input type="text"  name="lname" value="<?php echo isset($_POST["lname"]) ? $_POST["lname"] : "";?>"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="lname_validate_error"></span>
</td>
</tr>
<tr> 
<td>стать:</td>
<td><span style="color: red;">*</span> 
    чоловіча<input type="radio" name="sex" value="male" <?php if ((isset($_POST["sex"]) ? $_POST["sex"] : "") == "male") echo "checked"?>/>&nbsp&nbsp
    жіноча<input type="radio" name="sex" value="female" <?php if ((isset($_POST["sex"]) ? $_POST["sex"] : "") == "female") echo "checked"?>/>
	&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="sex_validate_error"></span>
</td>
</tr>
<tr> 
<td>електронна адреса</td>
<td><span style="color: red;">*</span><input type="text"  name="email" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : "";?>"/>
&nbsp&nbsp&nbsp&nbsp<span class="validate_error" id="email_validate_error"></span>
</td>
</tr>
</table><br><br>

<input type="submit" name="to_reg" value="зареєструватись"/>&nbsp&nbsp&nbsp
<input type="button" value="відмінити реєстрацію" onclick="location.href='../'"/>
</form>

</body>
</html>