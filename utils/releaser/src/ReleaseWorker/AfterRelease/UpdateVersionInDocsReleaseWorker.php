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
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Update current version in mkdocs.yaml config';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
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

    /**
     * @param string $fileContent
     * @return string
     */
    private function replaceCurrentVersionInMkdocsConfig(string $fileContent): string
    {
        // the "current_version" is under the "extra" key in mkdocs.yml, so it has indentation
        $updatedFileContent = preg_replace(
            '/^(\s*)current_version: .+$/m',
            '$1current_version: ' . $this->currentBranchName,
            $fileContent,
        );

        if ($updatedFileContent === null) {
            throw new RuntimeException('Failed to update current version in mkdocs.yml config');
        }

        return $updatedFileContent;
    }
}
