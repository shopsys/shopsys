<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UpdateUpgradeReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator $versionUpgradeFileManipulator
     */
    public function __construct(
        private readonly VersionUpgradeFileManipulator $versionUpgradeFileManipulator,
    ) {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Prepare the upgrading file for the release.';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->updateUpgradeFileWithReleasedVersion($version, $initialBranchName);

        $this->symfonyStyle->success(Message::SUCCESS);
        $this->symfonyStyle->note(
            'Review all the upgrading files whether they satisfy our rules and guidelines, see https://docs.shopsys.com/en/latest/contributing/guidelines-for-writing-upgrade/.',
        );
        $versionString = $version->getOriginalString();
        $this->symfonyStyle->note(sprintf(
            'Typically, you need to:
            - check the correctness of the order of Shopsys packages and sections,
            - check whether there are no duplicated instructions for modifying docker related files,
            - check links whether they point to the repository in the "%s" version
            - make sure, that every subsection of UPGRADE notes has link to correct pull request
            - check "see #project-base-diff to update your project" is added to the all the upgrade notes and add the line where it is missing (at least to all the Storefront entries)
            - replace all occurrences of #project-base-diff with link to project-base commit of the change
                - you can find the commit hashes quickly by executing following on the project-base repository:
                  git log --oneline --format="%%H %%s" | grep -E \'\(#[0-9]+\)$\'',
            $versionString,
        ));

        $this->confirm('Confirm that all subsections of UPGRADE notes has their links to correct pull request.');
        $this->confirm('Confirm that all #project-base-diff occurrences has been replaced by correct project-base commit links.');
        $this->confirm('Confirm that all upgrading files are ready for the release.');

        $this->commit(sprintf('upgrade files are now updated for %s release', $version->getVersionString()));
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    private function updateUpgradeFileWithReleasedVersion(Version $version, string $initialBranchName): void
    {
        $upgradeFilePath = getcwd() . '/UPGRADE-' . $initialBranchName . '.md';
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->versionUpgradeFileManipulator->processFileToString(
            $upgradeFileInfo,
            $version,
            $initialBranchName,
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);

        $this->processRunner->run('git add .');
    }
}
