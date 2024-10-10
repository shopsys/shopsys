<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\Json;
use PharIo\Version\Version;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\IntervalEvaluator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class ValidateConflictsInComposerJsonReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var string
     */
    private const string CONFLICT_SECTION = 'conflict';

    private const array IGNORED_CONFLICT_PACKAGES = [
        'symfony/symfony' => '*',
    ];

    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     * @param \Shopsys\Releaser\IntervalEvaluator $intervalEvaluator
     */
    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        private readonly IntervalEvaluator $intervalEvaluator,
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
        return 'Make sure that "conflict" versions in all composer.json files are closed interval';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $isPassing = true;

        foreach ($this->composerJsonFilesProvider->provideAll() as $fileInfo) {
            $jsonContent = Json::decode($fileInfo->getContents(), Json::FORCE_ARRAY);

            if (!isset($jsonContent[self::CONFLICT_SECTION])) {
                continue;
            }

            foreach ($jsonContent[self::CONFLICT_SECTION] as $packageName => $packageVersion) {
                if (
                    array_key_exists($packageName, self::IGNORED_CONFLICT_PACKAGES) &&
                    self::IGNORED_CONFLICT_PACKAGES[$packageName] === $packageVersion
                ) {
                    continue;
                }

                if ($this->intervalEvaluator->isClosedInterval($packageVersion)) {
                    continue;
                }

                $this->symfonyStyle->warning(sprintf(
                    '"%s" section in "%s" file has open version format for "%s": "%s".%sIt should be closed, e.g. "version|version2".',
                    self::CONFLICT_SECTION,
                    $fileInfo->getPathname(),
                    $packageName,
                    $packageVersion,
                    PHP_EOL,
                ));

                $isPassing = false;
            }
        }

        if ($isPassing) {
            $this->success();
        } else {
            $this->confirm('Confirm conflict versions are changed to specific versions or closed interval');
        }
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
