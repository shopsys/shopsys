<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateApiaryBlueprintReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     */
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Generate Apiary.io blueprint "php phing frontend-api-generate-apiary-blueprint" and commit it';
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->processRunner->run('php phing frontend-api-generate-apiary-blueprint');

        if ($this->hasGeneratedBlueprint()) {
            $this->commit('generated Apiary.io blueprint');

            $this->confirm(
                'Confirm that you checked generated blueprint and the changes are committed'
            );
        } else {
            $this->symfonyStyle->success('There are no changes in blueprint');
        }
    }

    /**
     * @return bool
     */
    private function hasGeneratedBlueprint(): bool
    {
        return !$this->isGitWorkingTreeEmpty();
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
