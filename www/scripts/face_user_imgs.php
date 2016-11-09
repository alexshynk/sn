<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"/>
<link rel="stylesheet"  type="text/css" href="../css/style.css"/>
<script type="text/javascript" src="../js/jquery-1.11.3.min.js"></script>
<title>user's images</title>
<style>
table#t1{border-collapse: separate; border-spacing: 10px 20px;}
td{padding: 0px; margin-left: 15px; position: relative;}
a{padding: 0px; margin: 0px;}
img{width: 200px; height: 200px;}
textarea{width: 100%; height: 4em; vertical-align: top; resize: none;}
input[value="редагувати"]{position: absolute; right: 0;}
</style>

<script type="text/javascript">
$(document).ready(function(){
	$("#load_imgs").on("submit",function(e){
		$("#loading_img").show();
		e.preventDefault();
		
		try{
		$.ajax({
			method: "POST",
			url: "user_imgs_upload.php",
			data: new FormData(this),
			contentType: false,
			processData: false,
			cache: false,
			async: false
			,success: function(data){
				if (data.indexOf("err_code==0") ==-1){
					$("#err_msg").html((data).replace(/\n/g,"<br>"));
					$("#err_msg").show(); 	 
				}
				else location.reload();
		    }
		});
		}
		catch(e){
			alert("Помилка "+e.name+":"+e.message+"\n"+e.stack);
		}
		$("#loading_img").hide();
	});
	
	$("#loading_img").hide();
	$("#err_msg").hide();
});


function fdel_img(image_id){
var conf = confirm("Видалити зображення?");
if (conf)
try{	
	$.ajax({
		method: "GET",
		url: "user_imgs_del.php",
		data: {"image_id":image_id},
		async: false,
		dataType: "json",
		success: function(data){
			if (data.err_code != 0){
				$("#err_msg").html((data.err_msg).replace(/\n/g,"<br>"));
				$("#err_msg").show(); 	
			}
			else location.reload();
		}
	});
}
catch(e){
	alert("Помилка "+e.name+":"+e.message+"\n"+e.stack);
}	
}

function fed_img(image_id){
	var desc = document.getElementById("desc"+image_id).value;
	desc = desc.substr(0, 250);
	
	$.ajax({
		method: "GET",
		url: "user_imgs_edit.php",
		data: {"image_id":image_id,"desc":desc},
		async: false,
		dataType: "json",
		success: function(data){
			if (data.err_code != 0){
				$("#err_msg").html((data.err_msg).replace(/\n/g,"<br>"));
				$("#err_msg").show(); 				
			}
			else location.reload();
		}
	});
}	
function fenable_edit(image_id){
	document.getElementById('bed'+image_id).disabled=false;
}

function fcheck_f_amount(allowed_f_count){
	var count = document.getElementById("user_pic").files.length;
	if (allowed_f_count-count >= 0)
	document.getElementById("do_load_imgs").disabled = false;
	else document.getElementById("do_load_imgs").disabled = true;
}

</script>
</head>
<body>
<?php include "top_banner.php"?>
<?php
require_once "error_handler.php";
require_once "common.php";
connect_to_db();
$allowed_f_count = get_allowed_images_count($_SESSION["user_id"]);
$file_hint = "Максимальна кількість файлів до завантаження: ".get_max_user_images_count().
             "<br>Доступно файлів до завантаження: ".$allowed_f_count;
?>
<center><h3>Зображення</h3></center>
<form id="load_imgs" action="" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>
<input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION["user_id"]; ?>"/>
Завантажити нові зображення (JPG, PNG)<br>
<div class="tooltip" style="width: 1em;">
<input type="file" id="user_pic" name="user_pic[]" multiple accept="image/jpeg,image/png" size=100 title=""
 onchange="fcheck_f_amount(<?php echo $allowed_f_count;?>)"/>
<span class="hint"><?php echo $file_hint;?></span>
</div><br>
<input type="submit" id="do_load_imgs" name="do_load_imgs" value="Завантажити" disabled/>&nbsp&nbsp
<span id="loading_img" style="font-weight: bold;">Завантаження ...</span>
<br>
</form>
<div id="err_msg"></div>
<hr></hr>
<?php
$query = sprintf("select l.image_id, i.filename, i.image_description from link_img_to_usr l, images i where l.image_id = i.image_id and l.user_id=%d order by i.image_id;",$_SESSION["user_id"]);
$result = mysql_query($query);
if (!$result) die("<p>Помилка при отриманні списку зображень користувача: ".mysql_error()."</p>");
echo "<center><table id='t1'>";
$i=0;
$cols = 4;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$i++;
	if ($i % $cols == 1) echo "<tr>";	
	echo "<td style='width:200px;'>";
	echo "<a href='../img/{$row["filename"]}'> <img src=get_img.php?image_id={$row["image_id"]}/> </a>";
	echo "<textarea id='desc{$row["image_id"]}' onkeypress='fenable_edit({$row["image_id"]})'>{$row["image_description"]}</textarea><br>";
	echo "<input type='button' value='видалити' onclick='fdel_img({$row["image_id"]})'/>";
	echo "<input id='bed{$row["image_id"]}' type='button' value='редагувати' onclick='fed_img({$row["image_id"]})' disabled/>";
	echo "</td>";
	if ($i % $cols == 0) echo "</tr>";
}
echo "</table></center>";
?>

</body>
<script>
	var tooltips = document.getElementsByClassName("tooltip");
	for(i=0; i<tooltips.length; i++){
		
		tooltips[i].addEventListener("mousemove", function(event){
			$(this).children("span").css({"left":event.offsetX+17, "top":event.offsetY + 17})
			}
			);
	}
</script>
</html>