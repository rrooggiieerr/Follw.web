<?php
// These database settings should be good enough for a local test environment
// Override settings in config.php for public testing and production environments
$servername = '127.0.0.1';
$dbname = 'follw';
$username = 'root';
$password = NULL;

@include_once('config.php');

require_once('controllers/main.php');

// If request could not be handeled
http_response_code(404);
exit();