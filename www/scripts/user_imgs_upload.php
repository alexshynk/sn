<?php
header("Content-Type: text/html;charset=utf-8");
require_once "error_handler.php";
require_once "common.php";
require_once "class_img.php";
session_start();
try{
	connect_to_db();
	$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : -1;
	if (!($user_id > 0)) throw new Exception("Не вказано user_id користувача");
	
	if ((isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1) !=  $user_id)
		throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");
	
	$file_error = array(0 => 'File is correct',
						1 => 'Maximum file size in php.ini exceeded',
						2 => 'Maximum file size in HTML form exceeded',
						3 => 'Only part of the file was uploaded',
						4 => 'No file was selected to upload.');
	
	//кількість зображень до завантаження
	$count = count($_FILES["user_pic"]["tmp_name"]);
	
	//перевірка кількості зображень яку ще можна завантажити
	$allowed_count = get_allowed_images_count($user_id);
	if (($allowed_count - $count) < 0) throw new Exception("Помилка: перевищено дозволений ліміт кількості зображень. \n Доступно до завантаження ще ".$allowed_count." зображень");
	
	
	for($i=0; $i<$count; $i++){
		mysql_query("START TRANSACTION");
		
		$err_code = isset($_FILES["user_pic"]["error"][$i]) ? $_FILES["user_pic"]["error"][$i] : -1;
		$err_msg = isset($file_error[$err_code]) ? $file_error[$err_code] : "undefined file loading error code: ".$err_code;
		if($err_code != 0) throw new Exception($err_msg);	
		
		if(!@is_uploaded_file($_FILES["user_pic"]["tmp_name"][$i])) throw new Exception("Помилка: аватар був завантаженый не через HTTP POST");
		
		$new_img = new ex_img_pic;
		$new_img->initialise_img($_FILES["user_pic"]["tmp_name"][$i], $_FILES["user_pic"]["type"][$i]);
		$new_img->convert_img(200,200,1000,1000);
		
		//зберігаємо в базу мініатюру
		$img_file_name = "img_".$user_id.date("YmdHis").$i.".".pathinfo($_FILES["user_pic"]["name"][$i],PATHINFO_EXTENSION);
		$img_description = str_replace("_"," ",pathinfo($_FILES["user_pic"]["name"][$i], PATHINFO_FILENAME));
		$query = "insert into images(mime_type, file_size, image_data, filename, image_description) ".	
		"values('{$_FILES["user_pic"]["type"][$i]}', {$new_img->ico_size}, '{$new_img->ico_data}', '{$img_file_name}','{$img_description}');";
		
		$result = mysql_query($query);
		if (!$result) throw new Exception("Помилка: ".mysql_error());
		$image_id = mysql_insert_id();
		
		//прив'язуемо зображення до користувача
		$query = sprintf("insert into link_img_to_usr(image_id, user_id) values(%d, %d);",$image_id, $user_id);
		$result = mysql_query($query);
		if (!$result) throw new Exception("Помилка: ".mysql_error());
		
		//зберігаємо у файл зображення
		imagejpeg($new_img->img_new, "../img/{$img_file_name}");
		
		unset($new_img);
		
		mysql_query("COMMIT");
	}
	$err_code = 0;
	$err_msg = "err_code==0";
}
catch(Exception $e){
	mysql_query("ROLLBACK");
	$err_code = -1;
	$err_msg = format_error($e);
	to_log($err_msg,1);
}
echo $err_msg;
exit;
?>