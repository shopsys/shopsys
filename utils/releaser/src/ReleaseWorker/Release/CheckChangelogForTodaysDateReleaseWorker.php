<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use LogicException;
use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\ChangelogFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CheckChangelogForTodaysDateReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FileManipulator\ChangelogFileManipulator $changelogFileManipulator
     */
    public function __construct(
        private readonly ChangelogFileManipulator $changelogFileManipulator,
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
        return sprintf(
            'Check the release date of "%s" version is "%s" in CHANGELOG.md. If necessary, the date is updated and the change is committed to "%s" branch',
            $version->getVersionString(),
            $this->getTodayAsString(),
            $this->createBranchName($version),
        );
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $changelogFilePath = getcwd() . '/CHANGELOG-' . $initialBranchName . '.md';
        $todayInString = $this->getTodayAsString();



        /**
         * @see https://regex101.com/r/izBgtv/6
         */
        $pattern = '#\#\# \[' . preg_quote($version->getOriginalString(), '#') . '\]\(.*\) \((\d+-\d+-\d+)\)#';

        try {
            $smartFileInfo = new SmartFileInfo($changelogFilePath);
            $fileContent = $smartFileInfo->getContents();

            $match = Strings::match($fileContent, $pattern);

            if ($match === null) {
                throw new LogicException('Release headline not found in file');
            }
        } catch (FileNotFoundException) {
            $this->symfonyStyle->error(sprintf('Unable to find file "%s".', $changelogFilePath));
            $this->renderCommonError();

            return;
        } catch (LogicException) {
            $this->symfonyStyle->error(sprintf(
                'Unable to find current release headline in file "%s".',
                $changelogFilePath,
            ));
            $this->renderCommonError();

            return;
        }

        if ($todayInString !== $match[1]) {
            $newChangelogContent = $this->changelogFileManipulator->updateReleaseDateOfCurrentReleaseToToday(
                $fileContent,
                $pattern,
                $todayInString,
            );
            FileSystem::write($changelogFilePath, $newChangelogContent);

            $infoMessage = sprintf(
                $smartFileInfo->getFilename() . ' date for "%s" version was updated to "%s".',
                $version->getVersionString(),
                $todayInString,
            );
            $this->symfonyStyle->note($infoMessage);

            $this->commit($infoMessage);
        }

        $this->symfonyStyle->success(Message::SUCCESS);
    }

    /**
     * @return string
     */
    private function getTodayAsString(): string
    {
        return (new DateTime())->format('Y-m-d');
    }

    private function renderCommonError(): void
    {
        $this->symfonyStyle->error('You need to check the release date in the appropriate changelog file manually.');

        $this->confirm('Confirm you have manually checked the release date in the appropriate changelog file');

        $this->symfonyStyle->success(Message::SUCCESS);
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
