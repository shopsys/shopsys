<?php

if ($argc < 2) {
    echo "Error: version is not set. Pass the version as argument.\n";
    exit(1);
}

$version = $argv[1];
$upgradeNotesPath = __DIR__;
$layoutFile = "{$upgradeNotesPath}/_layout.md";
$outputFile = "UPGRADE-{$version}.md";

const BACKEND_NOTES_PLACEHOLDER = '<!-- backendNotes -->';
const STOREFRONT_NOTES_PLACEHOLDER = '<!-- storefrontNotes -->';

function concatenateFiles($prefix, $upgradeNotesPath) {
    $content = "";
    $files = glob("{$upgradeNotesPath}/{$prefix}*");
    $lastIndex = count($files) - 1;

    foreach ($files as $index => $file) {
        if (is_file($file)) {
            $content .= file_get_contents($file);
            // Add an empty line after each file except the last one
            if ($index != $lastIndex) {
                $content .= "\n\n";
            } else {
                $content .= "\n";
            }
        }
    }
    return $content;
}

function deleteFiles($prefix, $upgradeNotesPath) {
    $files = glob("{$upgradeNotesPath}/{$prefix}*");

    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

if (!file_exists($layoutFile)) {
    echo "Error: layout file does not exist.\n";
    exit(1);
}

// If upgrade notes file for this version exists extend it, otherwise use template
if (file_exists($outputFile)) {
    $upgradeNotesContent = file_get_contents($outputFile);
} else {
    $upgradeNotesContent = file_get_contents($layoutFile);
}

$backendNotes = concatenateFiles("backend_", $upgradeNotesPath);
$upgradeNotesContent = str_replace(BACKEND_NOTES_PLACEHOLDER, $backendNotes . BACKEND_NOTES_PLACEHOLDER, $upgradeNotesContent);

$storefrontNotes = concatenateFiles("storefront_", $upgradeNotesPath);
$upgradeNotesContent = str_replace(STOREFRONT_NOTES_PLACEHOLDER, $storefrontNotes . STOREFRONT_NOTES_PLACEHOLDER, $upgradeNotesContent);

if (file_put_contents($outputFile, $upgradeNotesContent) === false) {
    echo "Error: could not write to output file.\n";
    exit(1);
}

// Delete the upgrade notes files after merging
deleteFiles("backend_", $upgradeNotesPath);
deleteFiles("storefront_", $upgradeNotesPath);

echo "Output written to $outputFile\n";
echo "Upgrade notes files deleted.\n";
