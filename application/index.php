<?php
// Fixes false "Variable is never used" validation errors
/* @var array $configuration */

// Override settings in config.php for public testing and production environments
$configuration = [
	'mode' => 'testing',
	'database' => [
		// These database settings should be good enough for a local test environment
		'driver' => 'mysql',
		'host' => '127.0.0.1',
		'dbname' => 'follw',
		'username' => 'root',
		'password' => NULL
	],
	'id' => [
		// Length in bytes of the unique share or follow ID
		// 8 bytes equals 64 bits equals 1.84467440737e+19 possible IDs
		'nBytes' => 8,
		'baseEncoding' => 62,
		'hashAlgorithm' => 'md5',
		'cipher' => 'bf'
	],
	'captcha' => [
		'enabled' => FALSE,
		'id' => $_SERVER['HTTP_HOST']
	],
	'jsonoptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
	'features' => [
		'share' => [
			// Progressive Web App
			'pwa' => FALSE,
			// Links to the app store for native app
			'app' =>  NULL
		],
		'follow' => [
			// Progressive Web App
			'pwa' => FALSE,
			// Links to the app store for native app
			'app' =>  NULL
		]
	]
];

// The above configuration parameters can be overridden in 
@include_once('config.php');

require_once('controllers/main.php');