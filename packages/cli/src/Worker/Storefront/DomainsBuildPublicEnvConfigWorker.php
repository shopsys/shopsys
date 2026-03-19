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

final class DomainsBuildPublicEnvConfigWorker extends AbstractWorker
{
    private const string FILE_PATH = 'storefront/buildPublicEnvConfig.ts';

    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Updates domains array in storefront/buildPublicEnvConfig.ts';
    }

    #[Override]
    public function getPriority(): int
    {
        return 488;
    }

    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $content = $this->fileHandler->readFile($filePath);

        $content = $this->replaceDomainUrlDeclarations($content, $config);
        $content = $this->replaceDomainUrlsValidation($content, $config);
        $content = $this->replaceDomainsArray($content, $config);

        $this->fileHandler->writeFile($filePath, $content);

        return WorkerResult::success(
            'Updated buildPublicEnvConfig.ts domains',
            filesModified: [self::FILE_PATH],
        );
    }

    private function replaceDomainUrlDeclarations(string $content, CoreProjectConfig $config): string
    {
        $declarations = [];

        foreach ($config->domains as $domain) {
            $declarations[] = sprintf("    const domainUrl%d = process.env.DOMAIN_HOSTNAME_%d ?? '';", $domain->id, $domain->id);
        }

        $pattern = '/(const sentryDsn.*\n)\n(\s*const domainUrl\d+.*\n)+/';
        $replacement = '${1}' . "\n" . implode("\n", $declarations) . "\n";

        $result = preg_replace($pattern, $replacement, $content);

        if ($result === null) {
            throw new RuntimeException('Unable to replace domain URL declarations in buildPublicEnvConfig.ts');
        }

        return $result;
    }

    private function replaceDomainUrlsValidation(string $content, CoreProjectConfig $config): string
    {
        $entries = [];

        foreach ($config->domains as $domain) {
            $entries[] = sprintf('DOMAIN_HOSTNAME_%d: domainUrl%d', $domain->id, $domain->id);
        }

        $pattern = '/const domainUrls = \{[^}]+\};/';
        $replacement = 'const domainUrls = { ' . implode(', ', $entries) . ' };';

        $result = preg_replace($pattern, $replacement, $content);

        if ($result === null) {
            throw new RuntimeException('Unable to replace domainUrls validation object in buildPublicEnvConfig.ts');
        }

        return $result;
    }

    private function replaceDomainsArray(string $content, CoreProjectConfig $config): string
    {
        $prototypeData = $this->extractDomainPrototype($content);
        $domainsCode = $this->generateDomainsCode($config, $prototypeData);
        $pattern = '/(domains:\s*)\[\n[\s\S]*?\n\s*]/';
        $replacement = '${1}' . $domainsCode;

        $result = preg_replace($pattern, $replacement, $content);

        if ($result === null) {
            throw new RuntimeException('Unable to replace domains block in buildPublicEnvConfig.ts');
        }

        return $result;
    }

    /**
     * @return array{prototype: string, closingIndent: string}
     */
    private function extractDomainPrototype(string $content): array
    {
        if (!preg_match('/domains:\s*\[\n((\s+)\{[\s\S]*?\n\2}),[\s\S]*?\n(\s*)],/', $content, $match)) {
            throw new RuntimeException('Unable to find domains array in buildPublicEnvConfig.ts');
        }

        return [
            'prototype' => $match[1],
            'closingIndent' => $match[3],
        ];
    }

    /**
     * @param array{prototype: string, closingIndent: string} $prototypeData
     */
    private function generateDomainsCode(CoreProjectConfig $config, array $prototypeData): string
    {
        $domains = [];

        foreach ($config->domains as $domain) {
            $domains[] = $this->generateDomainFromPrototype($domain, $prototypeData['prototype']);
        }

        return "[\n" . implode(",\n", $domains) . ",\n" . $prototypeData['closingIndent'] . ']';
    }

    private function generateDomainFromPrototype(CoreDomainConfig $domain, string $prototype): string
    {
        $mapSettings = $domain->getConfigSection(MapSettingsSection::class);

        $replacements = [
            '/PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_\d+/' => 'PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_' . $domain->id,
            '/DOMAIN_HOSTNAME_\d+/' => 'DOMAIN_HOSTNAME_' . $domain->id,
            '/domainUrl\d+/' => 'domainUrl' . $domain->id,
            '/defaultLocale:\s*[\'"][\w-]+[\'"]/' => "defaultLocale: '" . $domain->locale . "'",
            '/currencyCode:\s*[\'"][\w]+[\'"]/' => "currencyCode: '" . $domain->currencyCode . "'",
            '/fallbackTimezone:\s*[\'"][\w\/]+[\'"]/' => "fallbackTimezone: '" . $domain->timezone . "'",
            '/domainId:\s*\d+/' => 'domainId: ' . $domain->id,
            '/latitude:\s*[\d.]+/' => 'latitude: ' . $mapSettings->latitude,
            '/longitude:\s*[\d.]+/' => 'longitude: ' . $mapSettings->longitude,
            '/zoom:\s*\d+/' => 'zoom: ' . $mapSettings->zoom,
            "/includes\(['\"]\\d+['\"]\)/" => "includes('" . $domain->id . "')",
            '/GTM_ID_\d+/' => 'GTM_ID_' . $domain->id,
            '/type:\s*CustomerUserAreaEnum\.\w+/' => 'type: CustomerUserAreaEnum.' . strtoupper($domain->type),
        ];

        $result = $prototype;

        foreach ($replacements as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result);
        }

        return $result;
    }
}
