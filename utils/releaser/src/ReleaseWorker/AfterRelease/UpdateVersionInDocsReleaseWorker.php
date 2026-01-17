<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Nette\Utils\FileSystem;
use Override;
use PharIo\Version\Version;
use RuntimeException;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class UpdateVersionInDocsReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Update current version in mkdocs.yaml config';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $mkdocsConfigFilePath = getcwd() . '/mkdocs.yml';
        $fileContent = FileSystem::read($mkdocsConfigFilePath);
        $updatedFileContent = $this->replaceCurrentVersionInMkdocsConfig($fileContent);

        FileSystem::write($mkdocsConfigFilePath, $updatedFileContent);
        $this->commit('update current version in mkdocs.yaml config');
        $this->confirm(
            sprintf('Confirm you have pushed the new commit into the "%s" branch', $this->currentBranchName),
        );
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }

    private function replaceCurrentVersionInMkdocsConfig(string $fileContent): string
    {
        $updatedFileContent = preg_replace(
            '/current_version: .+$/m',
            'current_version: ' . $this->currentBranchName,
            $fileContent,
        );

        if ($updatedFileContent === null) {
            throw new RuntimeException('Failed to update current version in mkdocs.yml config');
        }

        return $updatedFileContent;
    }
}
