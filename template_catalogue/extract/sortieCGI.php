<?php

require_once 'conf/conf.php';

function send_to_cgi($requeteXml, &$retour){
	$data = "requete=".urlencode($requeteXml);
	try {
		$retour = do_post_request(EXTRACT_CGI,$data,'Content-Type: application/x-www-form-urlencoded;');
		return true;
	} catch (Exception $e) {
		$retour = $e->getMessage();
		return false;
	}
}

function send_to_cgi_fichiers($requeteXml, &$retour){
	$data = "requete=".urlencode($requeteXml);
	try {
		$retour = do_post_request(EXTRACT_CGI_FICHIERS,$data,'Content-Type: application/x-www-form-urlencoded;', 30);
		return true;
	} catch (Exception $e) {
		$retour = $e->getMessage();
		return false;
	}
}


function do_post_request($url, $data, $optional_headers = null, $timeout = 5){
	$params = array('http' => array(
			'method' => 'POST',
			'content' => $data,
			'timeout' => $timeout
	));
	if ($optional_headers!== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}

	//$meta = stream_get_meta_data($fp);
	//var_dump($meta);

	fclose($fp);
	return $response;
}

?>