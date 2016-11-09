<?php
class ex_img{
	public $tmp_name;		//шлях до завантаженого файлу зображення
	public $mime_type;		//mime тип зображення
	protected $img;			//завантажене зображення
	protected $img_width;	//ширина завантаженого зображення
	protected $img_height;	//висота завантаженного зображення
	public $img_new;		//нове зображення отримане в результаті конвертації
	protected $img_new_w;	//ширина нового зображення для конвертації
	protected $img_new_h;	//висота нового зображення для конвертації
	private $tmp_file = "../tmp/img.tmp"; //файл тимчасового зображення

	function initialise_img($tmp_name, $mime_type){
		$this->tmp_name = $tmp_name;
		$this->mime_type = $mime_type;
		
		//перевіряємо, що файл - зображення
		if (!@getimagesize($tmp_name)) throw new Exception("Помилка: завантажений аватар не є зображенням");
	}
	
	//завантаження зображення з файлу
	function load_img(){		
		if ($this->mime_type == "image/jpeg") $this->img = ImageCreateFromJpeg($this->tmp_name);
		else if ($this->mime_type == "image/png") $this->img = ImageCreateFromPng($this->tmp_name);
		
		//розміри оригінала
		$this->img_width = imagesx($this->img);	$this->img_height = imagesy($this->img);		
	}
	
	//нове пусте зображення
	function new_empty_img(){
		//створюємо нове зображення
		$this->img_new = ImageCreateTrueColor($this->img_new_w, $this->img_new_h);
		
		//зберігаємо прозорість фону для PNG, инакше фон буде чорним
		if($this->mime_type == "image/png"){
			//встановлюємо прозорість
			$transparent = imagecolorallocatealpha($this->img_new, 0, 0, 0, 127);
			imagecolortransparent($this->img_new, $transparent);
			//не змішувати шври зображень - верхній шар перекриває нижній
			imagealphablending($this->img_new, false);
			//зберегти всю альфа компоненту
			imagesavealpha($this->img_new, true);
		}
	}
	
	//конвертація зображення до нових розмірів
	function resize_img(){
		//пропорція
		$scale = $this->img_new_h/$this->img_new_w;
	
		//область вирізання зображення
		if($this->img_width*$scale == $this->img_height){ //якщо пропорції вірні і обрізка зображення не потрібна
			$img_x = 0; $img_y = 0; $img_w = $this->img_width; $img_h = $this->img_height;
		}
		else if($this->img_width*$scale > $this->img_height){ //якщо зображення ширше
			$img_x = round(($this->img_width - $this->img_height/$scale)/2); $img_y = 0;
			$img_w = round($this->img_height/$scale); $img_h = $this->img_height;
		}
		else if($this->img_width*$scale < $this->img_height){ //якщо зображення вище
			$img_x = 0; $img_y = round(($this->img_height - $this->img_width*$scale)/2); 
			$img_w = $this->img_width; $img_h = round($this->img_width*$scale);
		}
		
		//копіюємо область вирізання в нове зображення з масштабуванням
		ImageCopyResampled($this->img_new, $this->img, 0, 0, $img_x, $img_y, $this->img_new_w, $this->img_new_h, $img_w, $img_h);		
	}
	
	//отримуємо зображення у вигляді необхідному для збереження в базу
	function img_to_str(&$ico_size){
		if ($this->mime_type == "image/jpeg") imagejpeg($this->img_new, $this->tmp_file);
		else if ($this->mime_type == "image/png") imagepng($this->img_new, $this->tmp_file);
		$ico_size=filesize($this->tmp_file);
		
		$v = mysql_real_escape_string(file_get_contents($this->tmp_file));
		unlink($this->tmp_file);
		imagedestroy($this->img_new);
		return $v;
	}
}


class ex_img_ico extends ex_img{
	public $ico_size;		//розмір зображення(аватара)
	public $ico_data;		//зображення(аватар) у строковому форматі для збереження в БД
	
	//конвертація зображення
	function convert_img($ico_width = 150, $ico_height = 150){
		$this->img_new_w = $ico_width; $this->img_new_h = $ico_height;
		$this->load_img();
		$this->new_empty_img();
		$this->resize_img();
		$this->ico_data = $this->img_to_str($this->ico_size);
	}
}


class ex_img_pic extends ex_img_ico{
	//конвертація зображення
	function convert_img($ico_width, $ico_height, $img_max_width, $img_max_height){
		parent::convert_img($ico_width, $ico_height);
		
        if ($this->img_width > $this->img_height){
			$this->img_new_w = $img_max_width;
			$this->img_new_h = $this->img_new_w * $this->img_height / $this->img_width;
		}
		else{
			$this->img_new_h = $img_max_height;
			$this->img_new_w = $this->img_new_h * $this->img_width / $this->img_height;
		}

		$this->new_empty_img();
		$this->resize_img();
	}
}

?>