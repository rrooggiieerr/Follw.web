<?php
global $configuration;
global $protocol;

$lastmodified = filemtime(__FILE__);
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && date('D, d M Y H:i:s T', $lastmodified) === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
	http_response_code(304);
	exit();
}

header('Content-Type: text/plain');
header('Last-Modified: ' . date('D, d M Y H:i:s T', $lastmodified));
if($configuration['mode'] === 'production') {
	print('User-agent: *
Disallow: /generatesharingid');
	print('Sitemap: ' . $protocol . $_SERVER['HTTP_HOST'] . '/sitemap.xml');
} else {
	// Allow social media sites and deny everyone else
	print('User-agent: facebookexternalhit
Allow: /
Disallow:

User-agent: Twitterbot
Allow: /
Disallow:

User-agent: WhatsApp
Allow: /
Disallow:

User-agent: SkypeUriPreview Preview
Allow: /
Disallow:

User-agent: *
Disallow: /');
}