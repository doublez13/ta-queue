<?php
define("LDAP_SERVER", "");
define("LDAP_DOMAIN", "");
define("BIND_USER",   "");
define("BIND_PASSWD", "");
define("BASE_OU",     "");
define("ADMIN_GROUP", "");

define("SQL_SERVER",  "");
define("SQL_USER",    "");
define("SQL_PASSWD",  "");
define("DATABASE",    "");

/*
 *This flag signifies that "TLS_REQCERT never" has been
 *added to the ldap.conf file to disable cert checking.
 *
 *If this flag is not set, LDAP will fall back to 
 *plaintext auth, which is NOT RECOMMENDED.
 *
 *This flag is meaningless on php >= 7.0.5, since we
 *force the flag to be set via php-ldap
 */
define("TLS_REQCERT_DISABLED", true);
?>
