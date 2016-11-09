<?php
header("Content-Type: text/html;charset=utf-8");

require_once "error_handler.php";
require_once "common.php";
require_once "class_img.php";
session_start();
try{
	connect_to_db();
	mysql_query("START TRANSACTION");
	
	$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : -1;
	if (!($user_id > 0)) throw new Exception("Не вказано user_id користувача");
	
	if ((isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1) !=  $user_id)
		throw new Exception("Запит відхилено  - не відповідність параметрів сесії і переданих даних");	
	
	$file_error = array(0 => 'File is correct',
						1 => 'Maximum file size in php.ini exceeded',
						2 => 'Maximum file size in HTML form exceeded',
						3 => 'Only part of the file was uploaded',
						4 => 'No file was selected to upload.');					
	$err_code = isset($_FILES["user_pic"]["error"]) ? $_FILES["user_pic"]["error"] : -1;
	$err_msg = isset($file_error[$err_code]) ? $file_error[$err_code] : "undefined file loading error code: ".$err_code;
	if($err_code != 0) throw new Exception($err_msg);	
	
	if(!@is_uploaded_file($_FILES["user_pic"]["tmp_name"])) throw new Exception("Помилка: аватар був завантаженый не через HTTP POST");
	
	$new_img = new ex_img_ico;
	$new_img->initialise_img($_FILES["user_pic"]["tmp_name"], $_FILES["user_pic"]["type"]);
	$new_img->convert_img();
	
	$image_id = isset($_POST["image_id"]) ? $_POST["image_id"] : -1;
	if ($image_id > 0){
		$query = "update images set mime_type = '{$_FILES["user_pic"]["type"]}', file_size = {$new_img->ico_size}, image_data = '{$new_img->ico_data}' where image_id = {$image_id};";

		$result = mysql_query($query);
		if (!$result) throw new Exception("Помилка при збереженні аватара: ".mysql_error());		
	}
	else
	{
		$query = "insert into images(filename, mime_type, file_size, image_data) ".
		"values('{$_FILES["user_pic"]["name"]}', '{$_FILES["user_pic"]["type"]}', {$new_img->ico_size}, '{$new_img->ico_data}');";
		$result = mysql_query($query);
		if (!$result) throw new Exception("Помилка при збереженні аватара: ".mysql_error());
		$image_id = mysql_insert_id();
		
		$query = sprintf("update users set image_id =%d where user_id=%d;",$image_id, $user_id);
		$result = mysql_query($query);
		if (!$result) throw new Exception("Помилка при оновлені даних користувача: ".mysql_error());
	}
	
	unset($new_img);
	mysql_query("COMMIT");
	
	$err_code = 0;
	$err_msg = "err_code==0";

}
catch(Exception $e){
	mysql_query("ROLLBACK");
	$err_code = -1;
	$err_msg = format_error($e);
}
echo $err_msg;
exit;
?>