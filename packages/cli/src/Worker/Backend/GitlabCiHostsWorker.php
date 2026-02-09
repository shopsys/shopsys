<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreDomainConfig;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class GitlabCiHostsWorker extends AbstractWorker
{
    private const string FILE_PATH = '.gitlab-ci.yml';

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
        return 'Updates HOSTS variable in .gitlab-ci.yml';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 856;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        try {
            $content = $this->fileHandler->readFile($filePath);

            $hostsValue = $this->buildHostsValue($config->domains);

            $pattern = '/(HOSTS:\s*).*/';
            $replacement = '$1' . $hostsValue;

            $updatedContent = preg_replace($pattern, $replacement, $content);

            if ($updatedContent === null) {
                return WorkerResult::failure('Failed to update HOSTS variable in ' . self::FILE_PATH);
            }

            $this->fileHandler->writeFile($filePath, $updatedContent);

            return WorkerResult::success(
                'Updated HOSTS variable in ' . self::FILE_PATH,
                filesModified: [self::FILE_PATH],
            );
        } catch (RuntimeException $e) {
            return WorkerResult::failure($e->getMessage());
        }
    }

    /**
     * @param array<\Shopsys\Cli\Config\CoreDomainConfig> $domains
     */
    private function buildHostsValue(array $domains): string
    {
        if (count($domains) === 0) {
            return '${HOST}';
        }

        $hosts = [];
        $usedPrefixes = [];

        foreach ($domains as $domain) {
            if ($domain->id === 1) {
                $hosts[] = '${HOST}';

                continue;
            }

            $prefix = $this->getHostPrefix($domain, $usedPrefixes);
            $usedPrefixes[] = $prefix;
            $hosts[] = $prefix . '.${HOST}';
        }

        return implode(', ', $hosts);
    }

    /**
     * @param array<string> $usedPrefixes
     */
    private function getHostPrefix(CoreDomainConfig $domain, array $usedPrefixes): string
    {
        if (!in_array($domain->locale, $usedPrefixes, true)) {
            return $domain->locale;
        }

        // Locale already used, append domain ID for uniqueness
        return $domain->locale . $domain->id;
    }
}
