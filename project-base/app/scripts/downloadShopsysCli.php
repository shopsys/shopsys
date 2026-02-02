#!/usr/bin/env php
<?php

declare(strict_types=1);

$pharName = 'shopsys.phar';
$pharPath = dirname(__DIR__) . '/' . $pharName;
$baseUrl = 'https://github.com/shopsys/cli/releases';

$tagOutput = shell_exec('git describe --tags --exact-match 2>/dev/null');
$branchOutput = shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null');
$version = trim($tagOutput ?? $branchOutput ?? '');

$downloaded = false;

if ($version !== '') {
    $versionedUrl = sprintf('%s/download/%s/%s', $baseUrl, $version, $pharName);
    echo sprintf('Downloading %s from %s...%s', $pharName, $versionedUrl, PHP_EOL);
    $downloaded = @copy($versionedUrl, $pharPath);

    if (!$downloaded) {
        echo sprintf('Version %s not found, falling back to latest release...%s', $version, PHP_EOL);
    }
}

if (!$downloaded) {
    $latestUrl = sprintf('%s/latest/download/%s', $baseUrl, $pharName);
    echo sprintf('Downloading %s from %s...%s', $pharName, $latestUrl, PHP_EOL);
    $downloaded = @copy($latestUrl, $pharPath);
}

if (!$downloaded) {
    echo sprintf('Failed to download %s. Please check your network connection.%s', $pharName, PHP_EOL);
    exit(1);
}

chmod($pharPath, 0755);
echo sprintf('Successfully downloaded %s to %s%s', $pharName, $pharPath, PHP_EOL);
