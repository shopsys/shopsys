<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use Override;
use Psr\Log\LoggerInterface;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Throwable;

class McpSchemaCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        protected readonly McpSchemaFileGenerator $mcpSchemaFileGenerator,
        protected readonly ExposedSchemaProvider $exposedSchemaProvider,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
        #[Autowire(service: 'monolog.logger.mcp')]
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<string>
     */
    #[Override]
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        if (!$this->mcpAvailabilityChecker->isAvailable()) {
            return [];
        }

        try {
            $this->mcpSchemaFileGenerator->generateSchemaFile();
        } catch (Throwable $throwable) {
            $this->logger->error('MCP schema generation during cache warmup failed.', [
                'schema_file_path' => $this->exposedSchemaProvider->getSchemaFilePath(),
                'exception' => $throwable,
            ]);
        }

        return [];
    }

    #[Override]
    public function isOptional(): bool
    {
        return false;
    }
}
