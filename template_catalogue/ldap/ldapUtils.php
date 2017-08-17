<?php

function md5_hashtoLdap($hashMd5){
	return '{MD5}'.base64_encode(pack("H*", $hashMd5));
}

function md5_ldapToHash($ldapMd5){
	$ldapMd5 = str_replace('{MD5}','',$ldapMd5);
	return implode(unpack("H*",base64_decode($ldapMd5)));
}

function ldap_md5($clear){
	return md5_hashtoLdap(md5($clear));
}

?>
