<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectRootDirectory = __DIR__ . '/..';

// change autoloading source for monorepo
if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
    $projectRootDirectory = __DIR__ . '/../../..';
}

require $projectRootDirectory . '/vendor/symfony/dotenv/Dotenv.php';
(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$CDN_API_KEY = $_ENV['CDN_API_KEY'] ?? null;
$CDN_API_SALT = $_ENV['CDN_API_SALT'] ?? null;
$CDN_DOMAIN = $_ENV['CDN_DOMAIN'] ?? '//';
$CDN_RESIZE_DISABLE = $_ENV['CDN_RESIZE_DISABLE'] ?? false;

$imagePath = $_SERVER['DOCUMENT_URI'] ?? '';
$IMAGE_URL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $imagePath;

if ($CDN_RESIZE_DISABLE === true) {
    header('Location: ' . $IMAGE_URL);
    exit();
}

$allowedImageSizes = [16, 24, 32, 48, 64, 96, 128, 256, 480, 768, 1024, 1440];
sort($allowedImageSizes);

$resize = $_GET['resize'] ?? 'fit';
$width = findExactOrClosestLargerOrLargestImageSize(isset($_GET['width']) ? max(0, (int) $_GET['width']) : 0, $allowedImageSizes);
$height = findExactOrClosestLargerOrLargestImageSize(isset($_GET['height']) ? max(0, (int) $_GET['height']) : 0, $allowedImageSizes);
$gravity = 'no';
$enlarge = 0;

if ($width === 0 && $height === 0) {
    header('Location: ' . $IMAGE_URL);
    exit();
}

if ($CDN_DOMAIN === '//' || $CDN_API_KEY === null || $CDN_API_SALT === null) {
    # see https://docs.imgproxy.net/usage/processing
    $imgProxyInternalUrl = $_ENV['IMG_PROXY_INTERNAL_URL'] ?? 'http://img-proxy:8080';
    $webserverInternalUrl = $_ENV['WEBSERVER_INTERNAL_URL'] ?? 'http://webserver:8080';
    $imageUrl = sprintf('%s/unsafe_signature/rs:%s:%s:%s:%s/g:%s/plain/%s/%s', $imgProxyInternalUrl, $resize, $width, $height, $enlarge, $gravity, $webserverInternalUrl, $imagePath);
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
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_USERAGENT => 'ImageProxy/1.0',
        CURLOPT_HTTPHEADER => [
            'Accept: ' . $_SERVER['HTTP_ACCEPT'],
        ]
    ]);
    $image = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200 || !$image) {
        header("HTTP/1.0 404 Not Found");
        exit;
    }

    $etag = '"' . md5($image) . '"';

    header('X-Robots-Tag: noindex, nofollow');

    // Support 304 Not Modified
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }

    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . strlen($image));
    header('Content-Disposition: inline');
    header('Cache-Control: public, max-age=604800');
    header("Last-Modified: " . gmdate('D, d M Y H:i:s') . ' GMT');
    header("ETag: $etag");

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

function findExactOrClosestLargerOrLargestImageSize(int $requestedImageSize, array $allowedImageSizes) {
    if ($requestedImageSize === 0) {
        return 0;
    }
    foreach ($allowedImageSizes as $size) {
        if ($requestedImageSize <= $size) {
            return $size;
        }
    }
    return end($allowedImageSizes);
}
