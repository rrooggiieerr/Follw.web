<?php
require_once(dirname(__DIR__) . '/libs/phpqrcode.php');

// Fixes false "Variable is undefined" validation errors
/* @var String $protocol */
/* @var ID $id */

header('Content-Type: image/svg+xml');

QRcode::svg($protocol . $_SERVER['HTTP_HOST'] . '/' . $id->encode());