<?php
require_once ("/sites/kernel/#MainProject/conf.php");

define("PEOPLE_BASE","ou=People,".LDAP_BASE);
define("PROJECT_BASE","ou=Project,".LDAP_BASE);
define("GROUP_BASE","ou=Group,".LDAP_BASE);
define("USER_CLASS", "user");
define("STUDENT_CLASS", "studentUser");
define("REGISTERED_USER_CLASS", "registeredUser");
define("PROJECT_CLASS", "project");
define("STATUS_PENDING", "pending");
define("STATUS_ACCEPTED", "registered");
define("STATUS_REJECTED", "rejected");
define("FORMAT_DATE", "Ymd");

?>
