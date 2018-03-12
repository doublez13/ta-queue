<?php
// File: logout.php
// SPDX-License-Identifier: GPL-3.0-or-later

session_start();
header('Content-Type: application/json');

//Clear session variables
$_SESSION = array();

$return = array(
    "authenticated" => False,
    "success" => "User logged out"
);
echo json_encode($return);
session_destroy();
?>
