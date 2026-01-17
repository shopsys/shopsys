<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class GenerateApiaryBlueprintReleaseWorker extends AbstractShopsysReleaseWorker
{
    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Generate Apiary.io blueprint "php phing frontend-api-generate-apiary-blueprint" and commit it';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->processRunner->run('php phing frontend-api-generate-apiary-blueprint');

        if ($this->hasGeneratedBlueprint()) {
            $this->commit('generated Apiary.io blueprint');

            $this->confirm(
                'Confirm that you checked generated blueprint and the changes are committed',
            );
        } else {
            $this->symfonyStyle->success('There are no changes in blueprint');
        }
    }

    private function hasGeneratedBlueprint(): bool
    {
        return !$this->isGitWorkingTreeEmpty();
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
