<?php
error_reporting(E_ALL & ~E_STRICT & ~E_COMPILE_WARNING & ~E_CORE_WARNING & ~E_WARNING);
ini_set('display_errors', 'On');  //виводити помилки на єкран

$errors = array(
	 E_ERROR				=>"E_ERROR"
	,E_WARNING				=>"E_WARNING"
	,E_PARSE				=>"E_PARSE"
	,E_NOTICE				=>"E_NOTICE"
	,E_CORE_ERROR			=>"E_CORE_ERROR" 
	,E_CORE_WARNING			=>"E_CORE_WARNING" 
	,E_COMPILE_ERROR		=>"E_COMPILE_ERROR"
	,E_COMPILE_WARNING		=>"E_COMPILE_WARNING"
	,E_USER_ERROR			=>"E_USER_ERROR"
	,E_USER_WARNING			=>"E_USER_WARNING"
	,E_USER_NOTICE			=>"E_USER_NOTICE"
	,E_STRICT				=>"E_STRICT"
	,E_RECOVERABLE_ERROR	=>"E_RECOVERABLE_ERROR"
	,E_DEPRECATED			=>"E_DEPRECATED"
	,E_USER_DEPRECATED		=>"E_USER_DEPRECATED"
	,E_ALL					=>"E_ALL"
);

//файл логу фатальних помилок
$fatal_error_log = dirname(__DIR__)."/logs/fatal_error_".date("Ymd").".log";

//файл логу не фатальних помилок
$non_fatal_error_log = dirname(__DIR__)."/logs/non_fatal_error_".date("Ymd").".log";

//функція обробки фатальних помилок по завершенню роботи скрипта
function fatalErrorShutdownHandler(){
	$last_error = error_get_last();
	if ($last_error == null) return;
	
	//зберігаємо фатальну помилку в файл
	if (in_array($last_error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR,  E_USER_ERROR, E_RECOVERABLE_ERROR))){
		global $errors;
		global $fatal_error_log;		
		
		$res = "";
		$res = $res. "\r\n--------------------------------------------------------\r\n";
		$res = $res. date("Y.m.d G:i:s")."\r\n";
		$res = $res. $errors[$last_error['type']].": ". $last_error["message"].
				"\r\n ".basename($last_error["file"]).": {$last_error["line"]} line";
		error_log($res, 3, $fatal_error_log);
	}
}

//реєструємо функцію, що виконається по завершенню скрипта
register_shutdown_function('fatalErrorShutdownHandler');


// функція перехоплення не фатальних помилок
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $errors;
    if (!(error_reporting() & $errno)) return false; //Цей код помилки не включено в error_reporting

	if (in_array($errno,array(E_WARNING, E_NOTICE, E_CORE_WARNING, E_USER_WARNING, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED))){
		global $errors;
		global $non_fatal_error_log;		
		
		$res = "";
		$res = $res. "\r\n--------------------------------------------------------\r\n";
		$res = $res. date("Y.m.d G:i:s")."\r\n";
		$res = $res. $errors[$errno].": ". $errstr.
				"\r\n ".basename($errfile).": {$errline} line";
		error_log($res, 3, $non_fatal_error_log);
	}
	return true; //Після завершення функціїї не запускати внутрішній обробник помилок
}

//зареєструвати користувальницький обробник помилок
set_error_handler("myErrorHandler");
?>