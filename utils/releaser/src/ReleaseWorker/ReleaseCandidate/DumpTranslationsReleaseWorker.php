<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class DumpTranslationsReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Dump new translations with "php phing translations-dump" and commit them';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->processRunner->run('php phing translations-dump');

        if ($this->hasNewTranslations()) {
            if ($this->hasOnlyDeletedFiles()) {
                $this->commit('dump translations');
                $this->symfonyStyle->success('Translations were dumped and only deleted were found and committed');
            } else {
                $this->symfonyStyle->note(
                    'There are new translations, check the changed files (you can use "git status") command, fill in the missing translations and commit the changes',
                );
                $this->confirm(
                    'Confirm files are checked, missing translations completed and the changes are committed',
                );
            }
        } else {
            $this->symfonyStyle->success('There are no new translations');
        }
    }

    /**
     * @return bool
     */
    private function hasNewTranslations(): bool
    {
        return !$this->isGitWorkingTreeEmpty();
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    /**
     * @return bool
     */
    private function hasOnlyDeletedFiles(): bool
    {
        $allFilesStatus = $this->processRunner->run('git status --porcelain');
        $allFilesCount = $this->countFilesInStatus($allFilesStatus);

        $deletedFilesStatus = $this->processRunner->run('git ls-files -d');
        $deletedFilesCount = $this->countFilesInStatus($deletedFilesStatus);

        // has only deleted files or has also some modified/added files
        return $deletedFilesCount === $allFilesCount;
    }

    /**
     * @param string $filesStatus
     * @return int
     */
    private function countFilesInStatus(string $filesStatus): int
    {
        if ($filesStatus === '') {
            return 0;
        }

        return substr_count($filesStatus, "\n") + 1;
    }
}
