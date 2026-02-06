<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class DomainsInDeployWorker extends AbstractWorker
{
    private const string FILE_PATH = 'app/deploy/deploy-project.sh';

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
        return 'Updates DOMAIN_HOSTNAME_* in deploy-project.sh';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 928;
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

            $domainHostnames = [];

            foreach ($config->domains as $domain) {
                $domainHostnames[] = sprintf('        DOMAIN_HOSTNAME_%d', $domain->id);
            }

            $domainsArrayContent = implode("\n", $domainHostnames);

            $pattern = '/(DOMAINS=\(\n)(.*?)(\n\s*\))/s';
            $replacement = '$1' . $domainsArrayContent . '$3';

            $updatedContent = preg_replace($pattern, $replacement, $content);

            if ($updatedContent === null) {
                return WorkerResult::failure('Failed to update DOMAINS array in ' . self::FILE_PATH);
            }

            $this->fileHandler->writeFile($filePath, $updatedContent);

            return WorkerResult::success(
                'Updated DOMAIN_HOSTNAME_* in ' . self::FILE_PATH,
                filesModified: [self::FILE_PATH],
            );
        } catch (RuntimeException $e) {
            return WorkerResult::failure($e->getMessage());
        }
    }
}
