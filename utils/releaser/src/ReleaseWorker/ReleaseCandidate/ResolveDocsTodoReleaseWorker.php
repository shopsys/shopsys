<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symfony\Component\Finder\Finder;

final class ResolveDocsTodoReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @var string
     */
    private const string TODO_PLACEHOLDER = '<!--- TODO';

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return '[Manually] Resolve TODO comments in *.md files';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $fileInfos = $this->findMdFileInfos();

        $this->symfonyStyle->section(sprintf('Checking %d files for "%s"', count($fileInfos), self::TODO_PLACEHOLDER));

        $isPassing = true;

        foreach ($fileInfos as $fileInfo) {
            $todoFound = Strings::matchAll($fileInfo->getContents(), '#' . preg_quote(self::TODO_PLACEHOLDER) . '#');

            if ($todoFound === []) {
                continue;
            }

            $isPassing = false;

            $this->symfonyStyle->note(sprintf(
                'File "%s" has %d todo%s to resolve. Fix them manually.',
                $fileInfo->getPathname(),
                count($todoFound),
                count($todoFound) > 1 ? 's' : '',
            ));
        }

        if ($isPassing) {
            $this->success();
        } else {
            $this->confirm(
                sprintf(
                    'Confirm all todos in .md files are resolved and the changes are committed (you can use "documentation is now updated for %s release" commit message)',
                    $version->getVersionString(),
                ),
            );
        }
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    /**
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    private function findMdFileInfos(): array
    {
        $finder = Finder::create()->files()
            ->name('*.md')
            ->in(getcwd())
            ->exclude('vendor')
            ->exclude('project-base/app/var');

        return iterator_to_array($finder->getIterator());
    }
}
