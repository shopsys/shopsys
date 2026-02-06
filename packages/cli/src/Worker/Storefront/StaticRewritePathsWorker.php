<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class StaticRewritePathsWorker extends AbstractWorker
{
    private const string FILE_PATH = 'storefront/config/staticRewritePaths.ts';

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
        return 'Generates storefront/config/staticRewritePaths.ts with URL-to-routes mapping';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 416;
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
        $newLines = $this->replaceMappingLines($lines, $config);

        $this->fileHandler->writeFile($filePath, implode("\n", $newLines));

        return WorkerResult::success(
            'Generated staticRewritePaths.ts',
            filesModified: [self::FILE_PATH],
        );
    }

    /**
     * @param array<string> $lines
     * @return array<string>
     */
    private function replaceMappingLines(array $lines, CoreProjectConfig $config): array
    {
        $insertIndex = null;

        foreach ($lines as $index => $line) {
            if (str_contains($line, 'process.env.DOMAIN_HOSTNAME_')) {
                $insertIndex ??= $index;
                unset($lines[$index]);
            }
        }

        if ($insertIndex === null) {
            throw new RuntimeException('Could not find mapping lines in staticRewritePaths.ts');
        }

        $newMappingLines = [];

        foreach ($config->domains as $index => $domain) {
            $newMappingLines[] = $this->generateMappingLine($index, $domain->id);
        }

        $lines = array_values($lines);
        array_splice($lines, $insertIndex, 0, $newMappingLines);

        return $lines;
    }

    private function generateMappingLine(int $index, int $domainId): string
    {
        return sprintf(
            '    [(nextConfig?.publicRuntimeConfig?.domains?.[%d]?.url || process.env.DOMAIN_HOSTNAME_%d) as string]: routes[%d],',
            $index,
            $domainId,
            $index,
        );
    }
}
