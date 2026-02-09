<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class DomainsEnvWorker extends AbstractWorker
{
    private const string FILE_PATH = 'storefront/.env';

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
        return 'Updates DOMAIN_HOSTNAME_* and PUBLIC_GRAPHQL_ENDPOINT_* in storefront/.env';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 512;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $content = $this->fileHandler->readFile($filePath);
        $lines = explode("\n", $content);
        $internalEndpointLine = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/^DOMAIN_HOSTNAME_\d+=/', $line)) {
                unset($lines[$index]);
            }

            if (preg_match('/^PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_\d+=/', $line)) {
                unset($lines[$index]);
            }

            if (!str_starts_with($line, 'INTERNAL_ENDPOINT=')) {
                continue;
            }

            $internalEndpointLine = $line;
            unset($lines[$index]);
        }

        $domainHostnames = [];
        $graphqlEndpoints = [];

        foreach ($config->domains as $domain) {
            $placeholderUrl = $this->getPlaceholderUrl($domain->id);
            $domainHostnames[] = sprintf('DOMAIN_HOSTNAME_%d=%s', $domain->id, $placeholderUrl);
            $graphqlUrl = rtrim($placeholderUrl, '/') . '/graphql/';
            $graphqlEndpoints[] = sprintf('PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_%d=%s', $domain->id, $graphqlUrl);
        }

        $lines = [
            ...$domainHostnames,
            ...$graphqlEndpoints,
            ...$lines,
        ];

        if ($internalEndpointLine !== null) {
            $lines = [
                $internalEndpointLine,
                ...$lines,
            ];
        }

        $this->fileHandler->writeFile($filePath, implode("\n", $lines));

        return WorkerResult::success(
            'Domains for storefront set',
            filesModified: [self::FILE_PATH],
        );
    }

    private function getPlaceholderUrl(int $domainId): string
    {
        return sprintf('http://127.0.0.%d:8000/', $domainId);
    }
}
