<?php
//константи доступу
require_once dirname(dirname(__DIR__))."/sequre.php";

//налаштування системи
$conf = parse_ini_file(dirname(dirname(__DIR__))."/config.ini");

//отримати максимальну кількість файлів до завантаження яку може користувач завантажити собі на сторінку
function get_max_user_images_count(){global $conf;	return $conf["max_user_images_count"];}

function get_allowed_images_count($user_id){
	$query = sprintf("select ".get_max_user_images_count()." - count(1) from link_img_to_usr where user_id = %d;",$user_id);
	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка: ".mysql_error());
	$row = mysql_fetch_row($result);
	return $row[0];
}	

//підключитись до БД
function connect_to_db(){
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSW);
	if (!$link)
		throw new Exception("Помилка підключення до серверу MySQL: ".mysql_error());
	
	if (!@mysql_select_db(DB_NAME))
		throw new Exception("Помилка підключення до бази даних: ".mysql_error());
	
	//if (!@mysql_query("SET CHARACTER SET 'utf8'"))
	//	throw new Exception("Помилка встановлення кодуваняя".mysql_error());	
	
	if (!@mysql_query("SET names 'utf8'"))
		throw new Exception("Помилка встановлення кодуваняя ".mysql_error());	
	
	return $link;
	
	//if (!@mysql_query("charset 'utf8'"))
	//	throw new Exception("Помилка встановлення кодуваняя 3".mysql_error());	

	
}

//виконати запит до БД
function exec_query($query, &$err_msg){
	if($query || trim($query)=="") throw new Exception("Не задано запит");

	$result = mysql_query($query);
	if (!$result) throw new Exception("Помилка при виконанні запиту". mysql_error());

	if (preg_match("/^\s*(INSERT|UPDATE|DELETE)/i",$query)){
		$err_msg = mysql_affected_rows()." записів були оброблені";
	}
	elseif(preg_match("/^\s*(CREATE|DROP)/i",$query)){
		$err_msg = "DDL інструкція була успішно виконана";
	}
	else{
		$err_msg = "Запит до БД був успішно виконаний";
	}	
}

//записати в лог-файл
function msg_to_log($msg, $msg_type=0){
	$msg_types = array(
		0 => 'notice',
		1 => 'error'
	);
	
	$dst=fopen(dirname(__DIR__)."/logs/user_msg.log","a+");
	fputs($dst, date('Y M d H:i:s')."| ".
				basename($_SERVER["PHP_SELF"])."| ".
				$msg_types[$msg_type]."| ".
				$msg. "\r\n"
	);
	fclose($dst);
}

//записати в лог бд
function to_log($msg, $msg_type=0){
try{
	$script_name = basename($_SERVER["PHP_SELF"]);
	
	if (!isset($_SESSION)) session_start();
	$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : "null";
	
	$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.";charset=utf8;", DB_USER, DB_PASSW);
	$msg = $db->quote($msg);
	$db->beginTransaction();
	$query = "call to_log(@err_code, @err_msg, {$msg_type}, {$msg},'{$script_name}',{$user_id})";
	if (!$db->exec($query)){
		$errors = $db->errorInfo();
		if ($errors[0] != '00000')
			throw new Exception("Помилка при виконанні stored procedure to_log: SQLSTATE = {$errors[0]}, ERR_CODE = {$errors[1]}, ERR_MSG= {$errors[2]}");	
	}
	
	$select = $db->query("select @err_code, @err_msg");
	$result = $select->fetch();
	
	if ($result["@err_code"] != 0) 
	throw new Exception("Помилка при виконанні stored procedure to_log: @err_code = {$result["@err_code"]}, @err_msg = {$result["@err_msg"]}");
	
	$db->commit();
}
catch(Exception $e){
	$query = isset($query) ? "\n в запиті {$query}" : "";
	msg_to_log($e->getMessage().$query,1); 
	msg_to_log($msg);
}
}

function check_user_state($die=true){
	if (!isset($_SESSION)) session_start();
	$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : "null";
	
	$db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8;",DB_USER, DB_PASSW);
	$select=$db->prepare("call get_user_state(@err_code,@err_msg,@user_state,:user_id)");
	$select->execute(array("user_id" => $user_id));
	
	$select = $db->query("select @err_code,@err_msg,@user_state");
	$result = $select->fetch();
	
	if ($result["@err_code"] != 0) {
		die("<p style='color: red; font-size: large'>{$result["@err_msg"]}</a>");
	}
	
	if ($result["@user_state"] != 1){
		echo("<p style='color: red; font-size: large; padding: 20px;'>Ваш профіль був видалений</a>");
		if ($die) exit;
	}
}

function format_error($e){
	$res = $e->getMessage() ."\n in ".pathinfo($e->getFile(),PATHINFO_BASENAME). " on line ".$e->getLine();
	return $res;
}

function save_user_visit(){
	$http_referer = isset($_SERVER["HTTP_REFERER"]) ? "HTTP_REFERER=".$_SERVER["HTTP_REFERER"]."|" : "";	
	$remote_addr = isset($_SERVER["REMOTE_ADDR"]) ? "REMOTE_ADDR=".$_SERVER["REMOTE_ADDR"]."|" : "";
	if ((strpos(strtoupper($http_referer), strtoupper($_SERVER["HTTP_HOST"])) > 0)) return; //якщо редірект з сайту
	
	$script_name = pathinfo($_SERVER["PHP_SELF"],PATHINFO_FILENAME); 
	if (isset($_COOKIE[$script_name])) return; //якщо користувач вже відвідав сторінку за останні ...

	//фіксація факту відвідування
	to_log($remote_addr.$http_referer, 2);
	setcookie($script_name,"1",time()+10*60);
}

function send_email(&$err_msg, $to, $to_name, $subject, $body){
	try{
		global $conf;
		
		//если использовать для отправки писем HOST сервер
		if ((isset($conf["use_host_mail_server"]) ? $conf["use_host_mail_server"] : 0) == 1){
			$headers = "Content-type:text/text; charset=utf-8";
			if (mail($to, $subject, $body, $headers, "-fwww@{$_SERVER["HTTP_HOST"]}")) {
				$err_code = 0;
				$err_msg = "email був вдало відправлений";
			}
			else {
				$err_code = 1;
				$last_error=error_get_last();
				$err_msg = $last_error['type'].": ". $last_error["message"];
			}
			return $err_code;
		}
		
		require_once "../ext/PHPMailer_5.2.4/class.phpmailer.php";
		$mail = new PHPMailer();

		$mail->IsSMTP();                        // Set mailer to use SMTP
		$mail->Host = MAIL_HOST;  				// Specify main and backup server
		$mail->SMTPSecure = MAIL_SMTP_SECURE;	// Enable encryption
		$mail->Port = MAIL_SMTP_PORT;        	// Set the SMTP port
		if (trim(MAIL_SMTP_AUTH) != "")
			$mail->SMTPAuth = MAIL_SMTP_AUTH;  	// Enable SMTP authentication
		$mail->Username = MAIL_USERNAME;		// SMTP username
		$mail->Password = MAIL_PASSWORD;        // SMTP password

		$mail->From = MAIL_USERNAME;
		$mail->FromName = $_SERVER["HTTP_HOST"];

		$mail->AddAddress($to, $to_name); 

		$mail->Subject = $subject;
		$mail->Body    = $body;
		$mail->CharSet = 'utf-8';

		if(!$mail->Send()){
			$err_code = 1;
			$err_msg = "PHPMailer error: ".$mail->ErrorInfo;
		}
		else{
			$err_code = 0;
			$err_msg = "";
		}
	}
	catch(Exception $e){
		$err_code = 1;
		$err_msg = $e->getMessage();
	}
	
	return $err_code;
}	
?>