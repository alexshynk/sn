<?php
function do_json_response($err_msg){
	return json_encode(array("err_code"=>-1, "err_msg"=>$err_msg));
}

function do_html_response($err_msg){
	return "<div readonly style='background-color: #F5A9A9; height: 99%; width: 100%; word-wrap: break-word; overflow-y: auto;'>{$err_msg}</div>";
	error_log("text", 3, "test.txt");
}
?>