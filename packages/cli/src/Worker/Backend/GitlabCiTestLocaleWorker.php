<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class GitlabCiTestLocaleWorker extends AbstractWorker
{
    private const string FILE_PATH = '.gitlab-ci.yml';

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
        return 'Updates TEST_LOCALE variable in .gitlab-ci.yml to match first domain locale';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 808;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $firstDomainLocale = $config->domains[0]->locale;

        $content = $this->fileHandler->readFile($filePath);

        $pattern = '/TEST_LOCALE=\w+/';
        $replacement = 'TEST_LOCALE=' . $firstDomainLocale;

        $updatedContent = preg_replace($pattern, $replacement, $content, -1, $count);

        if ($updatedContent === null) {
            return WorkerResult::failure('Failed to update TEST_LOCALE variable in ' . self::FILE_PATH);
        }

        $this->fileHandler->writeFile($filePath, $updatedContent);

        return WorkerResult::success(
            sprintf('Updated %d TEST_LOCALE variable(s) to "%s" in %s', $count, $firstDomainLocale, self::FILE_PATH),
            filesModified: [self::FILE_PATH],
        );
    }
}
