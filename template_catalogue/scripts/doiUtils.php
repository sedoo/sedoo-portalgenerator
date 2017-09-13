<?php

// require_once('conf/doi.conf.php');

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
	} catch (Exception $e) {
                $retour = $e->getMessage();
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

        $params = array('http' => array(
                        'method' => "POST",
			'ignore_errors' => true,
			'header' => $header,
                        'content' => $data
        ));
    
        $ctx = stream_context_create($params);
        $fp = fopen($url, "rb", false, $ctx);

	echo "<br/>Code: ".$http_response_header[0];
        echo '<br/>';

        if ($fp === false) {
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
