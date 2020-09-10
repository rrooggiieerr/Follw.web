<?php
$servername = '127.0.0.1';
$dbname = 'iamhere';
$username = 'root';
$password = NULL;
$protcol = $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";