<?php

require_once ("mime_info.php");

function sendMailSimple($to,$sujet,$text,$from = null,$isHtml = false){
	
	$headers = '';
	if (isset($from)){
		$headers .= 'From: '.$from."\n";
	}
	$headers .= 'MIME-Version: 1.0'."\n";
	if ($isHtml == false)
		$headers .= "Content-type: text/plain; charset=UTF-8;";
	else
		$headers .= "Content-type: text/html; charset=UTF-8;";

	mail($to, $sujet, $text, $headers);
}

function sendMail($to,$sujet,$text,$attachments = null, $from = null){

	if (isset($attachments) && !empty($attachments)){

		if (! is_array($attachments)){
			$attachment = $attachments;
			$attachments = array();
			$attachments[] = $attachment;
		}
		
		$rand_key = md5(uniqid(mt_rand()));
		$frontiere = "==Multipart_Boundary_x{$rand_key}x";

		$headers = '';
		if (isset($from)){
			$headers .= 'From: '.$from."\n";
		}
		$headers .= 'MIME-Version: 1.0'."\n";
		$headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere."\"\n";


		$message = '--'.$frontiere."\n";
		$message .= 'Content-Type: text/plain; charset="UTF-8"'."\n";
		$message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
		$message .= $text;

		foreach ($attachments as $attachment){
			if (file_exists($attachment) && is_file($attachment)){
				$message .= "\n--".$frontiere."\n";

				/*
			 	 * mime_content_type : pas dispo sur medias3
			 	 */
				$message .= 'Content-Type: '.mime_content_type($attachment).'; name="'.basename($attachment)."\"\n";
				$message .= 'Content-Transfer-Encoding: base64'."\n";
				$message .= 'Content-Disposition:attachement; filename="'.basename($attachment).'"'."\n\n";

				$message .= chunk_split(base64_encode(file_get_contents($attachment)))."\n";
			}
		}

		$message .= "--$frontiere--";

		mail($to,$sujet,$message,$headers);


	}else{
		sendMailSimple($to,$sujet,$text,$from);
	}
}

?>
