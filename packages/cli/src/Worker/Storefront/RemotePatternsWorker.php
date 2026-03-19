<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class RemotePatternsWorker extends AbstractWorker
{
    private const string FILE_PATH = 'storefront/next.config.js';

    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Updates remotePatterns in storefront/next.config.js';
    }

    #[Override]
    public function getPriority(): int
    {
        return 487;
    }

    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $content = $this->fileHandler->readFile($filePath);
        $content = $this->replaceRemotePatterns($content, $config);
        $this->fileHandler->writeFile($filePath, $content);

        return WorkerResult::success(
            'Updated next.config.js remotePatterns',
            filesModified: [self::FILE_PATH],
        );
    }

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

    private function generateRemotePatterns(CoreProjectConfig $config): string
    {
        $patterns = [];

        foreach ($config->domains as $domain) {
            $patterns[] = "            {\n                hostname: process.env.DOMAIN_HOSTNAME_{$domain->id},\n            }";
        }

        return "[\n" . implode(",\n", $patterns) . ",\n        ]";
    }
}
