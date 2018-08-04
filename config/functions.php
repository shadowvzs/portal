<?php
function dd($var){
    echo'<pre>'.print_r($var, true).'</pre>';
	die;
}

function loadClass ($className){
    $classPath = ClASSES.$className.'.php';
    if (file_exists($classPath)){
        include($classPath);
    }
}

function getSession($key, $default=false){
	return $_SESSION[$key] ?? $default;
}

function setSession($key, $value) {
	if ($value == null) {
		unset($_SESSION[$key]);
	} else {
		$_SESSION[$key] = $value;
	}
}

function script($name) {
	return JS.DS.$name.'.js';
}

function css($name) {
	return CSS.DS.$name.'.css';
}


function generateToken($overwrite=false) {
	if (empty($_SESSION['token']) || $overwrite) {
		if (function_exists('mcrypt_create_iv')) {
			$_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
		} else {
			$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
		}
	}
}

function getToken(){
	return $_SESSION['token'];
}

function date2Mysql($date=null, $format = 'Y-m-d H:i:s') {
	return $data ? date($format, strtotime($date)) : date($format);
}

function fatal_error(){
	$error = error_get_last();
	if ($error !== NULL) {	
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		$dateNow = date('Y-m-d H:i:s');
		$trace = print_r( debug_backtrace( false ), true ) ?? "";
		$error_details="Date: {$dateNow} <br>Error: {$error["message"]}<br>Errno: {$error["type"]} <br>File: {$error["file"]} <br>Line: {$error["line"]} <br>Trace: {$trace}<br><br><br>";	
	}
	
	if (empty($error_details)) { return; }
		
	if (DEBUG) {
		echo $error_details;
	} else {
		echo "Sorry, fatal error... :(";
	}
	
	if (ERROR_LOG && $error_details) {
		$flag = file_exists(LOG_PATH) ? 'a' : 'w';
		$myfile = fopen(LOG_PATH, $flag);
		fwrite($myfile, str_replace('<br>', "\r\n", $error_details));
		fclose($myfile);
	}

}


function redirect($url) {
    header('Location: '.$url);
    exit;
}

function uploadImg ($file, $name='default.jpg'){

    $target_file = UPLOAD_DIR.$name;
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return true;
    } else {
       return false;
    }
}

function createThumbnail($filename, $final_width, $final_height){
    
    $oldFilePath=UPLOAD_DIR.$filename;
    $newFilePath=UPLOAD_DIR.'thumb_'.$filename;
    
    $image = imagecreatefromjpeg($oldFilePath);
    list($width_orig, $height_orig) = getimagesize($oldFilePath);
    
    $ratio = $width_orig/$height_orig;
    
    $new_width = floor($final_height * $ratio);
    if ($new_width >= $final_width) {
        $new_height = $final_height;
        $diff_x = floor(($new_width - $final_width) / 2);
        $diff_y = 0;
    }else{
        $new_width = $final_width;
        $new_height = floor($new_width / $ratio);
        $diff_x = 0;
        $diff_y = floor(($new_height - $final_height) / 2);    
    }
    
    $thumb = imagecreatetruecolor( $final_width, $final_height );
    
    imagecopyresampled($thumb,
                       $image,
                       -$diff_x,
                       -$diff_y,
                       0,
                       0,
                       $new_width, $new_height,
                       $width_orig, $height_orig);
    imagejpeg($thumb, $newFilePath, 80);

}