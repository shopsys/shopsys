<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectRootDirectory = __DIR__ . '/..';

// change autoloading source for monorepo
if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
    $projectRootDirectory = __DIR__ . '/../../..';
}

require $projectRootDirectory . '/vendor/symfony/dotenv/Dotenv.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$CDN_API_KEY = $_ENV['CDN_API_KEY'] ?? null;
$CDN_API_SALT = $_ENV['CDN_API_SALT'] ?? null;
$CDN_DOMAIN = $_ENV['CDN_DOMAIN'] ?? '//';

$IMAGE_URL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['DOCUMENT_URI'];

$resize = $_GET['resize'] ?? 'fit';
$width = $_GET['width'] ?? 0;
$height = $_GET['height'] ?? 0;
$gravity = 'no';
$enlarge = 1;

if ($CDN_DOMAIN === '//' || $CDN_API_KEY === null || $CDN_API_SALT === null) {
    # see https://docs.imgproxy.net/usage/processing
    $imgProxyInternalUrl = $_ENV['IMG_PROXY_INTERNAL_URL'] ?? 'http://img-proxy:8080';
    $webserverInternalUrl = $_ENV['WEBSERVER_INTERNAL_URL'] ?? 'http://webserver:8080';
    $imageUrl = sprintf('%s/unsafe_signature/rs:%s:%s:%s:%s/g:%s/plain/%s/%s', $imgProxyInternalUrl, $resize, $width, $height, $enlarge, $gravity, $webserverInternalUrl, $_SERVER['DOCUMENT_URI']);
} else {
    # see https://support.vshosting.cz/en/CDN/manipulating-images-in-cdn/
    $ttl = 1209600;

    $keyBin = pack("H*" , $CDN_API_KEY);
    if (empty($keyBin)) {
        die('Key expected to be hex-encoded string');
    }

    $saltBin = pack("H*" , $CDN_API_SALT);
    if (empty($saltBin)) {
        die('Salt expected to be hex-encoded string');
    }

    $extension = getExtension($IMAGE_URL);

    $encodedUrl = rtrim(strtr(base64_encode($IMAGE_URL), '+/', '-_'), '=');
    $path = "/{$resize}/{$width}/{$height}/{$gravity}/{$enlarge}/{$encodedUrl}.{$extension}";
    $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $saltBin."/".$ttl."/".$path, $keyBin, true)), '+/', '-_'), '=');

    $imageUrl = sprintf("%s/zoh4eiLi/IMG/%d/%s%s", $CDN_DOMAIN, $ttl, $signature, $path);
}

try {
    getImageFromUrl($imageUrl);
} catch (Throwable $throwable) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

function getImageFromUrl(string $url): void
{
    $image = file_get_contents($url);

    header('Content-type: image/' . getExtension($url));
    header('Content-Length: ' . strlen($image));
    header('Content-Disposition: inline');
    echo $image;
    exit;
}

function getExtension(string $url): string
{
    $extension = pathinfo($url, PATHINFO_EXTENSION);

    if ($extension !== 'jpg') {
        return $extension;
    }

    return 'jpeg';
}
