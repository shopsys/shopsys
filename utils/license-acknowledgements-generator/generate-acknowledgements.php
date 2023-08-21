<?php

declare(strict_types=1);

const TARGET_FILE = __DIR__ . '/../../open-source-license-acknowledgements-and-third-party-copyrights.md';

const PLACEHOLDER_BACKEND_NPM = '{{ placeholder.backendNpm }}';
const PLACEHOLDER_BACKEND_COMPOSER = '{{ placeholder.backendComposer }}';
const PLACEHOLDER_STOREFRONT_NPM = '{{ placeholder.storefrontNpm }}';

echo "Installing dependencies...";
installLicenseReport();
installPhpDependencies();
echo " ✓\n";

echo "Generating acknowledgements...\n";
copyTemplateFile();

echo "  - Backend NPM packages...";
generateNpm('app', PLACEHOLDER_BACKEND_NPM);
echo " ✓\n";

echo "  - Storefront NPM packages...";
generateNpm('storefront', PLACEHOLDER_STOREFRONT_NPM);
echo " ✓\n";

echo "  - Backend Composer packages...";
generateComposer();
echo " ✓\n";

####################################################################################################

function installLicenseReport(): void
{
    shell_exec('npm install -g --no-save license-report > /dev/null 2>&1');
}

function installPhpDependencies(): void
{
    shell_exec('composer install -q --working-dir=' . __DIR__);
    require_once __DIR__ . '/vendor/autoload.php';
}

function copyTemplateFile(): void
{
    copy(__DIR__ . '/template.md', TARGET_FILE);
}

function generateNpm(string $appName, string $placeholder): void
{
    $licenseReportCommand = [
        __DIR__ . '/../../.npm-global/bin/license-report',
        '--package=' . __DIR__ . '/../../project-base/' . $appName . '/package.json',
        '--config=' . __DIR__ . '/license-report-config.json',
        '--output=markdown',
    ];

    $licenses = shell_exec(implode(' ', $licenseReportCommand));

    replacePlaceHolderInTargetFile($placeholder, $licenses);
}

function generateComposer(): void
{
    $installed = json_decode(file_get_contents(__DIR__ . '/../../vendor/composer/installed.json'), true, 512, JSON_THROW_ON_ERROR);

    $tableBuilder = new \MaddHatter\MarkdownTable\Builder();

    $tableBuilder->headers(['Name', 'License Type', 'Homepage', 'Authors']);

    foreach ($installed['packages'] as $package) {

        $tableBuilder->row(
            [
                $package['name'],
                implode(', ', $package['license']),
                $package['homepage'] ?? '',
                formatAuthor($package['authors'] ?? null),
            ],
        );
    }

    replacePlaceHolderInTargetFile(PLACEHOLDER_BACKEND_COMPOSER, $tableBuilder->render());
}

function formatAuthor(?array $authors): string
{
    if ($authors === null) {
        return '';
    }

    $formattedAuthors = [];

    foreach ($authors as $author) {
        $authorName = $author['name'] ?? '';

        $homepage = $author['homepage'] ?? null;

        if ($homepage !== null) {
            $authorName .= ' (' . $homepage . ')';
        }

        $formattedAuthors[] = $authorName;
    }

    return implode(', ', $formattedAuthors);
}

function replacePlaceHolderInTargetFile(string $placeholder, string $value): void
{
    $fileContent = file_get_contents(TARGET_FILE);
    $fileContent = str_replace($placeholder, $value, $fileContent);
    file_put_contents(TARGET_FILE, $fileContent);
}
