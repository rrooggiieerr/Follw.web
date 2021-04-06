<?php
global $configuration;

$filename = dirname(__DIR__) . '/views/openapi.json';

$lastmodified = filemtime($filename);
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && date('D, d M Y H:i:s T', $lastmodified) === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
	http_response_code(304);
	exit();
}

header('Content-Type: application/json');
header('Last-Modified: ' . date('D, d M Y H:i:s T', $lastmodified));

$serverEnvironments = ['development' => 'Development server',
	'testing' => 'Test server',
	'production' => 'Production server'
];

$json = json_decode(file_get_contents($filename), true);

$json['info']['contact']['url'] = $configuration['baseurl'] . 'contact';
$json['info']['contact']['email'] = 'support@' . $_SERVER['HTTP_HOST'];
array_push($json['servers'], ['url' => $configuration['baseurl'],
		'description' => $serverEnvironments[$configuration['mode']]
]);
$json['components']['schemas']['id']['pattern'] = '^[' . $configuration['id']['encodedChars'] . ']{' . $configuration['id']['encodedLength'] . '}$';

print(json_encode($json, $configuration['jsonoptions']));