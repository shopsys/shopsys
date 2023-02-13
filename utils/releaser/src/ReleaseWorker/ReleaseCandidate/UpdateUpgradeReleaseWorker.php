<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\GeneralUpgradeFileManipulator;
use Shopsys\Releaser\FileManipulator\MonorepoUpgradeFileManipulator;
use Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symplify\SmartFileSystem\SmartFileInfo;
use Twig\Environment;

final class UpdateUpgradeReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var \Shopsys\Releaser\FileManipulator\MonorepoUpgradeFileManipulator
     */
    private $monorepoUpgradeFileManipulator;

    /**
     * @var \Shopsys\Releaser\FileManipulator\GeneralUpgradeFileManipulator
     */
    private $generalUpgradeFileManipulator;

    /**
     * @var \Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator
     */
    private $versionUpgradeFileManipulator;

    /**
     * @var \Twig\Environment
     */
    private $twigEnvironment;

    /**
     * @var string
     */
    private $nextDevelopmentVersionString;

    /**
     * @param \Shopsys\Releaser\FileManipulator\MonorepoUpgradeFileManipulator $monorepoUpgradeFileManipulator
     * @param \Shopsys\Releaser\FileManipulator\GeneralUpgradeFileManipulator $generalUpgradeFileManipulator
     * @param \Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator $versionUpgradeFileManipulator
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        MonorepoUpgradeFileManipulator $monorepoUpgradeFileManipulator,
        GeneralUpgradeFileManipulator $generalUpgradeFileManipulator,
        VersionUpgradeFileManipulator $versionUpgradeFileManipulator,
        Environment $twigEnvironment
    ) {
        $this->monorepoUpgradeFileManipulator = $monorepoUpgradeFileManipulator;
        $this->generalUpgradeFileManipulator = $generalUpgradeFileManipulator;
        $this->versionUpgradeFileManipulator = $versionUpgradeFileManipulator;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Prepare all upgrading files for the release.';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $this->nextDevelopmentVersionString = $this->askForNextDevelopmentVersion($version, true)->getOriginalString();

        $this->updateUpgradeFileForMonorepo($version);
        $this->createUpgradeFileForNewVersionFromDevelopmentVersion($version, $initialBranchName);
        $this->createUpgradeFileForNextDevelopmentVersion($version, $initialBranchName);
        $this->updateGeneralUpgradeFile($version);

        $this->symfonyStyle->success(Message::SUCCESS);
        $this->symfonyStyle->note(
            'Review all the upgrading files whether they satisfy our rules and guidelines, see https://docs.shopsys.com/en/latest/contributing/guidelines-for-writing-upgrade/.'
        );
        $versionString = $version->getOriginalString();
        $this->symfonyStyle->note(sprintf(
            'Typically, you need to:
            - check the correctness of the order of Shopsys packages and sections,
            - check whether there are no duplicated instructions for modifying docker related files,
            - change the links from master to the %1$s version in UPGRADE-%1$s.md file
            - make sure, that every subsection of UPGRADE notes has link to correct pull request
            - replace all occurrences of #project-base-diff with link to project-base commit of the change',
            $versionString
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
     */
    private function updateUpgradeFileForMonorepo(Version $version)
    {
        $upgradeFilePath = getcwd() . '/upgrade/upgrading-monorepo.md';
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->monorepoUpgradeFileManipulator->processFileToString(
            $upgradeFileInfo,
            $version,
            $this->currentBranchName,
            $this->nextDevelopmentVersionString
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    private function createUpgradeFileForNewVersionFromDevelopmentVersion(Version $version, string $initialBranchName)
    {
        $upgradeFilePath = getcwd() . '/upgrade/UPGRADE-' . $version->getOriginalString() . '-dev.md';
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->versionUpgradeFileManipulator->processFileToString(
            $upgradeFileInfo,
            $version,
            $initialBranchName
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
        FileSystem::rename($upgradeFilePath, getcwd() . '/upgrade/UPGRADE-' . $version->getOriginalString() . '.md');

        $this->processRunner->run('git add .');
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    private function updateGeneralUpgradeFile(Version $version)
    {
        $upgradeFilePath = getcwd() . '/UPGRADE.md';
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->generalUpgradeFileManipulator->updateLinks(
            $upgradeFileInfo,
            $version,
            $this->nextDevelopmentVersionString
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    private function createUpgradeFileForNextDevelopmentVersion(Version $version, string $initialBranchName)
    {
        $content = $this->twigEnvironment->render(
            'UPGRADE-next-development-version.md.twig',
            [
                'versionString' => $version->getOriginalString(),
                'initialBranchName' => $initialBranchName,
                'nextDevelopmentVersion' => $this->nextDevelopmentVersionString,
            ]
        );
        FileSystem::write(getcwd() . '/upgrade/UPGRADE-' . $this->nextDevelopmentVersionString . '.md', $content);
    }
}
