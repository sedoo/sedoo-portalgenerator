<?php

require_once('conf/doi.conf.php');

function createDoi($doi,$url,$metadata){
	if (uploadMetadataDoi($metadata)){
		updateDoi($doi,$url);
	}
}

function updateDoi($doi,$url){
	$data = array ('doi' => $doi, 'url' => $url);
	$data = http_build_query($data);
        try {
	        $retour = do_post_request(SERVICE_DOI_UPDATE,$data,"Content-Type: application/x-www-form-urlencoded;");
//                      return true;
	} catch (Exception $e) {
                $retour = $e->getMessage();
//                      return false;
        }
        echo "Retour: $retour<br/>";
}

function uploadMetadataDoi($metadata){
	try {
                $retour = do_post_request(SERVICE_DOI_UPLOAD_METADATA,$metadata,"Content-Type:application/xml;charset=UTF-8");
		echo "Retour: $retour<br/>";
                return true;
        } catch (Exception $e) {
                $retour = $e->getMessage();
		echo "Retour: $retour<br/>";
                return false;
        }
}

function do_post_request($url, $data, $header){

//	$authorization=base64_encode(SERVICE_DOI_USER.':'.SERVICE_DOI_PASSWD); 

        $params = array('http' => array(
                        'method' => "POST",
			'ignore_errors' => true,
			'header' => $header,//."\r\nAuthorization: Basic $authorization",
			/*'header'=> "Content-Type:text/plain;charset=UTF-8\r\n"
	                .'Content-Length: '.strlen($data)."\r\n",*/
                        'content' => $data
        ));
    //    echo '<br/>'.'<br/>';
//        print_r($params);
  //      echo '<br/>'.'<br/>';
        
//        echo $data;
//        echo '<br/>'.'<br/>';
        $ctx = stream_context_create($params);
        $fp = fopen($url, "rb", false, $ctx);
        //echo '<br/>'.'<br/>';
        //print_r($http_response_header);
	echo "<br/>Code: ".$http_response_header[0];
        echo '<br/>';

        if ($fp === false) {

		//echo 'last error: ';
  var_dump(error_get_last());

                throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = stream_get_contents($fp);
        if ($response === false) {
                throw new Exception("Problem reading data from $url, $php_errormsg");
        }

	fclose($fp);
        return $response;

}


?>
