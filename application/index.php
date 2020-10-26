<?php
// These database settings should be good enough for a local test environment
// Override settings in config.php for public testing and production environments
$servername = '127.0.0.1';
$dbname = 'follw';
$username = 'root';
$password = NULL;

// Length in bytes of the unique share or follow ID
// 8 bytes equals 64 bits equals 1.84467440737e+19 possible IDs
// Don't forget to modify the database model when changing this number
$idlength = 8;

@include_once('config.php');

require_once('controllers/main.php');

// If request could not be handeled
http_response_code(404);
exit();