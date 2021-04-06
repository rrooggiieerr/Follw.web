<?php
global $protocol;

header('Content-Type: application/xml');
print('<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL);
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    <url>
        <loc><?= $protocol . $_SERVER['HTTP_HOST'] ?>/</loc>
        <lastmod><?= date('Y-m-d', filemtime(dirname(__DIR__) . '/views/intro.php')) ?></lastmod>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= $protocol . $_SERVER['HTTP_HOST'] ?>/privacy</loc>
        <lastmod><?= date('Y-m-d', filemtime(dirname(__DIR__) . '/views/privacy.html')) ?></lastmod>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= $protocol . $_SERVER['HTTP_HOST'] ?>/terms</loc>
        <lastmod><?= date('Y-m-d', filemtime(dirname(__DIR__) . '/views/terms.html')) ?></lastmod>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= $protocol . $_SERVER['HTTP_HOST'] ?>/credits</loc>
        <lastmod><?= date('Y-m-d', filemtime(dirname(__DIR__) . '/views/credits.html')) ?></lastmod>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= $protocol . $_SERVER['HTTP_HOST'] ?>/apidoc</loc>
        <lastmod><?= date('Y-m-d', filemtime(dirname(__DIR__) . '/views/apidoc.html')) ?></lastmod>
        <changefreq>monthly</changefreq>
    </url>
</urlset>