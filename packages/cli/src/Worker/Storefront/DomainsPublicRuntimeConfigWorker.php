<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreDomainConfig;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Config\Section\MapSettingsSection;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class DomainsPublicRuntimeConfigWorker extends AbstractWorker
{
    private const string FILE_PATH = 'storefront/next.config.js';

    /**
     * @param \Shopsys\Cli\Model\FileHandler $fileHandler
     */
    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Updates domains array in publicRuntimeConfig and remotePatterns in storefront/next.config.js';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 488;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $content = $this->fileHandler->readFile($filePath);

        $content = $this->replaceRemotePatterns($content, $config);
        $content = $this->replaceDomainsArray($content, $config);

        $this->fileHandler->writeFile($filePath, $content);

        return WorkerResult::success(
            'Updated next.config.js domains and remotePatterns',
            filesModified: [self::FILE_PATH],
        );
    }

    /**
     * @param string $content
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @return string
     */
    private function replaceRemotePatterns(string $content, CoreProjectConfig $config): string
    {
        $remotePatterns = $this->generateRemotePatterns($config);
        $pattern = '/(remotePatterns:\s*)\[[\s\S]*?],/';
        $replacement = '${1}' . $remotePatterns . ',';

        $result = preg_replace($pattern, $replacement, $content);

        if ($result === null) {
            throw new RuntimeException('Unable to replace remotePatterns block in next.config.js');
        }

        return $result;
    }

    /**
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @return string
     */
    private function generateRemotePatterns(CoreProjectConfig $config): string
    {
        $patterns = [];

        foreach ($config->domains as $domain) {
            $patterns[] = "            {\n                hostname: process.env.DOMAIN_HOSTNAME_{$domain->id},\n            }";
        }

        return "[\n" . implode(",\n", $patterns) . ",\n        ]";
    }

    /**
     * @param string $content
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @return string
     */
    private function replaceDomainsArray(string $content, CoreProjectConfig $config): string
    {
        $prototypeData = $this->extractDomainPrototype($content);
        $domainsCode = $this->generateDomainsCode($config, $prototypeData);
        $pattern = '/(domains:\s*)\[\n[\s\S]*?\n\s*]/';
        $replacement = '${1}' . $domainsCode;

        $result = preg_replace($pattern, $replacement, $content);

        if ($result === null) {
            throw new RuntimeException('Unable to replace domains block in next.config.js');
        }

        return $result;
    }

    /**
     * @param string $content
     * @return array{prototype: string, closingIndent: string}
     */
    private function extractDomainPrototype(string $content): array
    {
        // Match first domain object only (ends with },) and closing bracket indentation
        if (!preg_match('/domains:\s*\[\n((\s+)\{[\s\S]*?\n\2}),[\s\S]*?\n(\s*)],/', $content, $match)) {
            throw new RuntimeException('Unable to find domains array in next.config.js');
        }

        return [
            'prototype' => $match[1],
            'closingIndent' => $match[3],
        ];
    }

    /**
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @param array{prototype: string, closingIndent: string} $prototypeData
     * @return string
     */
    private function generateDomainsCode(CoreProjectConfig $config, array $prototypeData): string
    {
        $domains = [];

        foreach ($config->domains as $domain) {
            $domains[] = $this->generateDomainFromPrototype($domain, $prototypeData['prototype']);
        }

        return "[\n" . implode(",\n", $domains) . ",\n" . $prototypeData['closingIndent'] . ']';
    }

    /**
     * @param \Shopsys\Cli\Config\CoreDomainConfig $domain
     * @param string $prototype
     * @return string
     */
    private function generateDomainFromPrototype(CoreDomainConfig $domain, string $prototype): string
    {
        $mapSettings = $domain->getConfigSection(MapSettingsSection::class);

        $replacements = [
            '/PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_\d+/' => 'PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_' . $domain->id,
            '/DOMAIN_HOSTNAME_\d+/' => 'DOMAIN_HOSTNAME_' . $domain->id,
            '/defaultLocale:\s*[\'"][\w-]+[\'"]/' => "defaultLocale: '" . $domain->locale . "'",
            '/currencyCode:\s*[\'"][\w]+[\'"]/' => "currencyCode: '" . $domain->currencyCode . "'",
            '/fallbackTimezone:\s*[\'"][\w\/]+[\'"]/' => "fallbackTimezone: '" . $domain->timezone . "'",
            '/domainId:\s*\d+/' => 'domainId: ' . $domain->id,
            '/latitude:\s*[\d.]+/' => 'latitude: ' . $mapSettings->latitude,
            '/longitude:\s*[\d.]+/' => 'longitude: ' . $mapSettings->longitude,
            '/zoom:\s*\d+/' => 'zoom: ' . $mapSettings->zoom,
            "/includes\(['\"]\\d+['\"]\)/" => "includes('" . $domain->id . "')",
            '/type:\s*[\'"][\w]+[\'"]/' => "type: '" . strtoupper($domain->type) . "'",
        ];

        $result = $prototype;

        foreach ($replacements as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result);
        }

        return $result;
    }
}
