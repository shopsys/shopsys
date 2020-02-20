<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\GeneralUpgradeFileManipulator;
use Shopsys\Releaser\FileManipulator\MonorepoUpgradeFileManipulator;
use Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symplify\MonorepoBuilder\Release\Message;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
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
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Prepare all upgrading files for the release.';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 800;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->nextDevelopmentVersionString = $this->askForNextDevelopmentVersion($version, true)->getVersionString();

        $this->updateUpgradeFileForMonorepo($version);
        $this->createUpgradeFileForNewVersionFromDevelopmentVersion($version);
        $this->createUpgradeFileForNextDevelopmentVersion($version);
        $this->updateGeneralUpgradeFile($version);

        $this->symfonyStyle->success(Message::SUCCESS);
        $this->symfonyStyle->note('Review all the upgrading files whether they satisfy our rules and guidelines, see https://docs.shopsys.com/en/latest/contributing/guidelines-for-writing-upgrade/.');
        $versionString = $version->getVersionString();
        $this->symfonyStyle->note(sprintf(
            'Typically, you need to:
            - check the correctness of the order of Shopsys packages and sections, 
            - check whether there are no duplicated instructions for modifying docker related files, 
            - change the links from master to the %1$s version in UPGRADE-%1$s.md file.',
            $versionString
        ));
        $this->symfonyStyle->note(sprintf('You need to commit the upgrade files manually with commit message "upgrade files are now updated for %s release" commit message.', $versionString));

        $this->confirm('Confirm all upgrading files are ready for the release and the changes are committed');
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

        $newUpgradeContent = $this->monorepoUpgradeFileManipulator->processFileToString($upgradeFileInfo, $version, $this->initialBranchName, $this->nextDevelopmentVersionString);

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    private function createUpgradeFileForNewVersionFromDevelopmentVersion(Version $version)
    {
        $upgradeFilePath = getcwd() . '/upgrade/UPGRADE-' . $version->getVersionString() . '-dev.md';
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->versionUpgradeFileManipulator->processFileToString($upgradeFileInfo, $version, $this->initialBranchName);

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
        FileSystem::rename($upgradeFilePath, getcwd() . '/upgrade/UPGRADE-' . $version->getVersionString() . '.md');

        $this->processRunner->run('git add .');
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    private function updateGeneralUpgradeFile(Version $version)
    {
        $upgradeFilePath = getcwd() . '/UPGRADE.md';
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->generalUpgradeFileManipulator->updateLinks($upgradeFileInfo, $version, $this->nextDevelopmentVersionString);

        FileSystem::write($upgradeFilePath, $newUpgradeContent);
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    private function createUpgradeFileForNextDevelopmentVersion(Version $version)
    {
        $content = $this->twigEnvironment->render(
            'UPGRADE-next-development-version.md.twig',
            [
                'versionString' => $version->getVersionString(),
                'initialBranchName' => $this->initialBranchName,
                'nextDevelopmentVersion' => $this->nextDevelopmentVersionString,
            ]
        );
        FileSystem::write(getcwd() . '/upgrade/UPGRADE-' . $this->nextDevelopmentVersionString . '.md', $content);
    }
}
