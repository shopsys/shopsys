<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\Json;
use Override;
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
        'guzzlehttp/psr7' => '<=1.8.3 || >=2.0.0 <=2.1.0',
    ];

    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
        private readonly IntervalEvaluator $intervalEvaluator,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Make sure that "conflict" versions in all composer.json files are closed interval';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $isPassing = true;

        foreach ($this->composerJsonFilesProvider->provideAll() as $fileInfo) {
            $jsonContent = Json::decode($fileInfo->getContents(), true);

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
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
