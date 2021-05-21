<?php
// Fixes false "Variable is never used" validation errors
/* @var array $configuration */

// Override settings in config.php for public testing and production environments
$configuration = [
	// One of development, testing, production
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
		'hashAlgorithm' => 'sha256',
		// Hash key needs to be unique to your instalation, set it in config.php
		//'hashKey' => '',
		'cipher' => 'aes256'
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
	],
	// When sharing a location on Twitter the Twitter Handle is used to link to the Follw Twitter Account
	'twitterhandle' => '@follw_app',
	'contactemail' => NULL
];

// The configuration parameters should be set or overridden in
include_once('config.php');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$configuration['baseurl'] = $protocol . $_SERVER['HTTP_HOST'] . '/';

require_once(dirname(__DIR__) . '/application/libs/Base.php');
$configuration['id']['encodedLength'] = BASE::length($configuration['id']['nBytes'], $configuration['id']['baseEncoding']);
$configuration['id']['encodedChars'] = BASE::chars($configuration['id']['baseEncoding']);
$configuration['id']['regexPattern'] = BASE::regexPattern($configuration['id']['nBytes'], $configuration['id']['baseEncoding']);

require_once('controllers/main.php');