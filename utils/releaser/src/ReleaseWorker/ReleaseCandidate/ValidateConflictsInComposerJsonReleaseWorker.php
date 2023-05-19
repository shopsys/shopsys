<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\IntervalEvaluator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;

final class ValidateConflictsInComposerJsonReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider
     */
    private $composerJsonFilesProvider;

    /**
     * @var \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager
     */
    private $jsonFileManager;

    /**
     * @var string
     */
    private const CONFLICT_SECTION = 'conflict';

    /**
     * @var \Shopsys\Releaser\IntervalEvaluator
     */
    private $intervalEvaluator;

    private const IGNORED_CONFLICT_PACKAGES = [
        'symfony/symfony' => '*',
    ];

    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager
     * @param \Shopsys\Releaser\IntervalEvaluator $intervalEvaluator
     */
    public function __construct(
        ComposerJsonFilesProvider $composerJsonFilesProvider,
        JsonFileManager $jsonFileManager,
        IntervalEvaluator $intervalEvaluator
    ) {
        $this->composerJsonFilesProvider = $composerJsonFilesProvider;
        $this->jsonFileManager = $jsonFileManager;
        $this->intervalEvaluator = $intervalEvaluator;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): string
    {
        return 'Make sure that "conflict" versions in all composer.json files are closed interval';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME): void
    {
        $isPassing = true;

        foreach ($this->composerJsonFilesProvider->provideAll() as $fileInfo) {
            $jsonContent = $this->jsonFileManager->loadFromFileInfo($fileInfo);

            if (!isset($jsonContent[self::CONFLICT_SECTION])) {
                continue;
            }

            foreach ($jsonContent[self::CONFLICT_SECTION] as $packageName => $version) {
                if (
                    array_key_exists($packageName, self::IGNORED_CONFLICT_PACKAGES) &&
                    self::IGNORED_CONFLICT_PACKAGES[$packageName] === $version
                ) {
                    continue;
                }

                if ($this->intervalEvaluator->isClosedInterval($version)) {
                    continue;
                }

                $this->symfonyStyle->warning(sprintf(
                    '"%s" section in "%s" file has open version format for "%s": "%s".%sIt should be closed, e.g. "version|version2".',
                    self::CONFLICT_SECTION,
                    $fileInfo->getPathname(),
                    $packageName,
                    $version,
                    PHP_EOL
                ));

                $isPassing = false;
            }
        }

        if ($isPassing) {
            $this->symfonyStyle->success(Message::SUCCESS);
        } else {
            $this->confirm('Confirm conflict versions are changed to specific versions or closed interval');
        }
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
