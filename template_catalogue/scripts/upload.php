<?php

require_once('conf/conf.php');

function uploadImg($fileBox){
	//print_r($_FILES);
	$imgPath = ATT_IMG_URL_PATH; 
	$tmp_file = $_FILES[$fileBox]['tmp_name'];
	//echo '<br/>Filename'.$_FILES[$fileBox]['name'].'<br>';
	//echo 'Temp file'.$_FILES[$fileBox]['tmp_name'].'<br>';
	if( !is_uploaded_file($tmp_file) ){
		echo "<font size=\"3\" color='red'><b>Image: File not found</b></font><br>";
	}else{
		if( preg_match('#[\x00-\x1F\x7F-\x9F/\\\\]#', $name_file) ){
			echo "<font size=\"3\" color='red'><b>Image: Filename not valid</b></font><br>";
		}else{
			$type_file = $_FILES[$fileBox]['type'];
			if( !strstr($type_file, 'jpg') && !strstr($type_file, 'jpeg') && !strstr($type_file, 'bmp') && !strstr($type_file, 'gif') && !strstr($type_file, 'png') ){
				echo "<font size=\"3\"  color='red'><b>Image: not a picture</b></font><br>";
			}else{
				$name_file = $_FILES[$fileBox]['name'];
												
				$new_name = strtolower(time().'-'.$name_file);
				//echo 'Dest name: '.$new_name.'<br>';
				
				if( !move_uploaded_file($tmp_file, WEB_PATH.$imgPath.$new_name) ){
					echo "<font size=\"3\" color='red'><b>Image: error while copying file</b></font><br>";
				}else{
					echo "<font size=\"3\" color='green'><b>Image: file succesfully uploaded</b></font><br>";
				//	echo "Uploaded file: ".$imgPath.$new_name;
					return $imgPath.$new_name;
				}
			}
		}
	}
	return null;	
}

function uploadDoc($fileBox){
	$tmp_file = $_FILES[$fileBox]['tmp_name'];

	echo 'Filename'.$_FILES[$fileBox]['name'].'<br>';
        echo 'Temp file'.$_FILES[$fileBox]['tmp_name'].'<br>';

	if( !is_uploaded_file($tmp_file) ){
                echo "<font size=\"3\" color='red'><b>Upload: File not found</b></font><br>";
        }else{
                if( preg_match('#[\x00-\x1F\x7F-\x9F/\\\\]#', $name_file) ){
                        echo "<font size=\"3\" color='red'><b>Upload: Filename not valid</b></font><br>";
                }else{
			$name_file = $_FILES[$fileBox]['name'];
			$new_name = time().'-'.$name_file;
			echo 'Dest name: '.$new_name.'<br>';
			if( !move_uploaded_file($tmp_file, ATT_FILES_PATH.'/'.$new_name) ){
                        	echo "<font size=\"3\" color='red'><b>Upload: error while copying file</b></font><br>";
                        }else{
                                echo "<font size=\"3\" color='green'><b>File succesfully uploaded</b></font><br>";				
				echo "Uploaded file: ".ATT_FILES_PATH.'/'.$new_name;
                                return $new_name;
                       }
		}
	}
	return null;
}

?>
