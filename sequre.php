<?php
//Параметри доступу до БД
if (!defined("DB_HOST")) define("DB_HOST", "db_host");			//["доменне ім'я"/"IP адреса"] серверу MySQL
if (!defined("DB_USER")) define("DB_USER", "db_user");  		//користувач MySQL
if (!defined("DB_PASSW")) define("DB_PASSW", "db_passw");		//пароль користувача
if (!defined("DB_NAME")) define("DB_NAME", "db_name");			//база даних

//Налаштування SMTP серверу
if (!defined("MAIL_HOST")) define("MAIL_HOST","smtp.gmail.com");  					//[доменне ім'я/IP адреса] SMTP серверу
if (!defined("MAIL_SMTP_SECURE")) define("MAIL_SMTP_SECURE","ssl");                	//шифрування: ["ssl"/"tsl"], "" - без шифрування
if (!defined("MAIL_SMTP_PORT")) define("MAIL_SMTP_PORT",465);                   	//порт
if (!defined("MAIL_SMTP_AUTH")) define("MAIL_SMTP_AUTH",true);                     	//включити аутентифікацію [true/false]
if (!defined("MAIL_USERNAME")) define("MAIL_USERNAME","user@gmail.com");			//користувач SMTP
if (!defined("MAIL_PASSWORD")) define("MAIL_PASSWORD","passw");          			//пароль користувача
?>