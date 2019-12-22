<?php
define("LDAP_SERVERS",[]);
define("LDAP_DOMAIN", "");
define("BIND_USER",   "");
define("BIND_PASSWD", "");
define("BASE_OU",     "");

define("SQL_SERVER",  "");
define("SQL_USER",    "");
define("SQL_PASSWD",  "");
define("DATABASE",    "");

//Auth must be LDAP or CAS
define("AUTH", "");

if(AUTH == "CAS"){
  $phpcas_path = '';
  $cas_host = '';
  $cas_context = '';
  $cas_port = 443;
  $cas_server_ca_cert_path = '';
}
?>
