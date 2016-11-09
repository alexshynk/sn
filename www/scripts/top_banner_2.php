<?php
session_start();

$user_login = isset($_SESSION["user_login"]) ? $_SESSION["user_login"] : "";

echo  <<<EOD
<div id="top_banner">
<input type="button"  value="на головну" onclick="location.href='../index.php'"/>
<span style="font-weight: bold; position: absolute; right: 25px; ">{$user_login}</span>
</div><br>
EOD;
?>