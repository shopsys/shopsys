<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Nette\Utils\FileSystem;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UpdateUpgradeReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const string REGEX_PATTERN_PR_LINK = '/\[#(\d+)\]\(https:\/\/github\.com\/shopsys\/shopsys\/pull\/\d+\)/';
    private const string SEE_PROJECT_BASE_DIFF_LINE = 'see #project-base-diff to update your project';
    private const string PROJECT_BASE_DIFF_LINK_TEXT = '[project-base diff]';

    private string $temporaryDirectoryPath;

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
        $this->processRunner->run(
            sprintf('php phing -D version=%s upgrade-merge', $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue()),
        );

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
            - make sure, that every subsection of UPGRADE notes has link to correct pull request',
            $versionString,
        ));

        $this->confirm('Confirm that all subsections of UPGRADE notes has their links to correct pull request.');

        $this->symfonyStyle->note(sprintf(
            '
Now will run process that automatically replaces #project-base-diff hashes with links.
During the process you might be asked to fill correct commit SHA1 as it might not be automatically retrieved.
This command (that needs to be run in project-base repository with %s branch) might help you in looking for correct commit:
git log --oneline --format="%%H %%s" | grep "<put_here_commit_message_of_merge_commit_from_pr>"',
            $initialBranchName,
        ));

        $this->updateUpgradeNotesWithProjectBaseDiffLinks($initialBranchName, $this->getPathToUpgradeFile($initialBranchName));
        $this->processRunner->run('php phing markdown-fix');

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
     * @param string $initialBranchName
     * @return string
     */
    private function getPathToUpgradeFile(string $initialBranchName): string
    {
        return getcwd() . '/UPGRADE-' . $initialBranchName . '.md';
    }

    /**
     * @param string $initialBranchName
     */
    private function cloneProjectBaseToTemporaryFolder(string $initialBranchName): void
    {
        $this->temporaryDirectoryPath = trim($this->processRunner->run('mktemp -d -t shopsys-upgrade-notes-XXXX'));

        $this->processRunner->run(
            sprintf(
                'git clone --bare --single-branch --branch=%s %s %s',
                $initialBranchName,
                'https://github.com/shopsys/project-base.git',
                $this->temporaryDirectoryPath,
            ),
        );
    }

    private function clearTemporaryFolder(): void
    {
        $this->processRunner->run('rm -rf ' . $this->temporaryDirectoryPath);
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    private function updateUpgradeFileWithReleasedVersion(Version $version, string $initialBranchName): void
    {
        $upgradeFilePath = $this->getPathToUpgradeFile($initialBranchName);
        $upgradeFileInfo = new SmartFileInfo($upgradeFilePath);

        $newUpgradeContent = $this->versionUpgradeFileManipulator->processFileToString(
            $upgradeFileInfo,
            $version,
            $initialBranchName,
        );

        FileSystem::write($upgradeFilePath, $newUpgradeContent);

        $this->processRunner->run('git add .');
    }

    /**
     * @param string $initialBranchName
     * @param string $pathToUpgradeNotes
     */
    private function updateUpgradeNotesWithProjectBaseDiffLinks(
        string $initialBranchName,
        string $pathToUpgradeNotes,
    ): void {
        if (!file_exists($pathToUpgradeNotes)) {
            throw new FileNotFoundException(path: $pathToUpgradeNotes);
        }

        $fileHandle = fopen($pathToUpgradeNotes, 'r');

        if (!$fileHandle) {
            return;
        }

        [$fileLines, $headlineLines] = $this->parseFileToLines($fileHandle);

        $this->cloneProjectBaseToTemporaryFolder($initialBranchName);

        $this->findAndReplaceProjectBaseDiffHashWithLinks($headlineLines, $fileLines);
        $this->findMissingProjectBaseDiffHashAndFillLinks($headlineLines, $fileLines);

        fclose($fileHandle);
        file_put_contents($pathToUpgradeNotes, $fileLines);

        $this->clearTemporaryFolder();
    }

    /**
     * @param $fileHandle
     * @return array
     */
    private function parseFileToLines($fileHandle): array
    {
        $lineNumber = 0;
        $fileLines = [];
        $headlineLines = [];

        while (($line = fgets($fileHandle)) !== false) {
            $line = trim($line, '"');

            if (str_starts_with($line, '####') && preg_match(self::REGEX_PATTERN_PR_LINK, $line) === 1) {
                $headlineLines[$lineNumber] = $line;
            }

            $fileLines[$lineNumber] = $line;
            $lineNumber++;
        }

        return [$fileLines, $headlineLines];
    }

    /**
     * @param array $lines
     * @param int $firstHeadlineNumber
     * @param int|null $secondHeadlineNumber
     * @return array
     */
    private function getLinesBetweenTwoHeadlines(
        array $lines,
        int $firstHeadlineNumber,
        ?int $secondHeadlineNumber,
    ): array {
        $lineNumbers = array_keys($lines);
        $startIndex = array_search($firstHeadlineNumber, $lineNumbers, true);

        if ($secondHeadlineNumber === null) {
            $endIndex = array_key_last($lines);
        } else {
            $endIndex = array_search($secondHeadlineNumber, $lineNumbers, true);
        }

        if ($startIndex === false || $endIndex === false) {
            return [];
        }

        $length = $endIndex - $startIndex + 1;

        return array_slice($lines, $startIndex, $length, true);
    }

    /**
     * @param string $line
     * @return array
     */
    private function parseCommitLinesByPrNumbers(string $line): array
    {
        $pattern = self::REGEX_PATTERN_PR_LINK;

        preg_match_all($pattern, $line, $prNumbersMatches);

        $commitLinesByPrNumber = [];

        foreach ($prNumbersMatches[1] as $prNumber) {
            try {
                $commitLinesRaw = $this->processRunner->run('cd ' . $this->temporaryDirectoryPath . ' && git log --oneline --format="%H %s" | grep -E "\(#' . $prNumber . '\)$"');
                $commitLines = explode(
                    PHP_EOL,
                    $commitLinesRaw,
                );

                array_pop($commitLines);

                $commitLinesByPrNumber[$prNumber] = $commitLines;
            } catch (ProcessFailedException) {
                $commitLinesByPrNumber[$prNumber] = null;

                continue;
            }
        }

        return $commitLinesByPrNumber;
    }

    /**
     * @param array $headlineLines
     * @param array $fileLines
     */
    private function findAndReplaceProjectBaseDiffHashWithLinks(array $headlineLines, array &$fileLines): void
    {
        foreach ($headlineLines as $headlineLineNumber => $headlineLine) {
            $commitLinesByPrNumber = $this->parseCommitLinesByPrNumbers($headlineLine);
            $nextHeadlineLineNumber = $this->getNextHeadlineLineNumber($headlineLines, $headlineLineNumber);
            $linesOfCurrentUpgradeNote = $this->getLinesBetweenTwoHeadlines($fileLines, $headlineLineNumber, $nextHeadlineLineNumber);
            $currentUpgradeNoteLineNumber = $headlineLineNumber;

            foreach ($linesOfCurrentUpgradeNote as $lineOfCurrentUpgradeNote) {
                if (str_contains($lineOfCurrentUpgradeNote, self::SEE_PROJECT_BASE_DIFF_LINE)) {
                    $this->replaceProjectBaseDiffHashWithLinks($fileLines, $currentUpgradeNoteLineNumber, $lineOfCurrentUpgradeNote, $commitLinesByPrNumber, $headlineLine);

                    continue;
                }

                $currentUpgradeNoteLineNumber++;
            }
        }
    }

    /**
     * @param array $headlineLines
     * @param array $fileLines
     */
    private function findMissingProjectBaseDiffHashAndFillLinks(array $headlineLines, array &$fileLines): void
    {
        $headlineLines = $this->addLastLineAsHeadlineToEnsureLastUpgradeNoteIsProcessed($fileLines, $headlineLines);

        foreach (array_reverse($headlineLines, true) as $headlineLineNumber => $headlineLine) {
            $previousHeadlineLineNumber = $this->getPreviousHeadlineLineNumber($headlineLines, $headlineLineNumber);

            if ($previousHeadlineLineNumber === null) {
                continue;
            }

            $currentHeadlineLine = $headlineLines[$previousHeadlineLineNumber];
            $revertedCommitLinesByPrNumber = array_reverse($this->parseCommitLinesByPrNumbers($currentHeadlineLine), true);
            $linesOfCurrentUpgradeNote = $this->getLinesBetweenTwoHeadlines($fileLines, $previousHeadlineLineNumber, $headlineLineNumber);
            $projectBaseDiffHashFound = false;

            foreach ($linesOfCurrentUpgradeNote as $lineOfCurrentUpgradeNote) {
                if (str_contains($lineOfCurrentUpgradeNote, self::PROJECT_BASE_DIFF_LINK_TEXT) || str_contains($lineOfCurrentUpgradeNote, self::SEE_PROJECT_BASE_DIFF_LINE)) {
                    $projectBaseDiffHashFound = true;
                }
            }

            if ($projectBaseDiffHashFound) {
                continue;
            }

            foreach ($revertedCommitLinesByPrNumber as $commitLines) {
                if ($commitLines !== null) {
                    foreach (array_reverse($linesOfCurrentUpgradeNote, true) as $lineNumber => $lineOfCurrentUpgradeNote) {
                        if (str_starts_with(PHP_EOL, $lineOfCurrentUpgradeNote)) {
                            $this->increaseLineNumberFrom($fileLines, $lineNumber);

                            $fileLines[$lineNumber] = '-   ' . self::SEE_PROJECT_BASE_DIFF_LINE . PHP_EOL;
                            $this->replaceProjectBaseDiffHashWithLinks($fileLines, $lineNumber, $fileLines[$lineNumber], $revertedCommitLinesByPrNumber, $currentHeadlineLine);

                            continue 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $fileLines
     * @param int $lineNumber
     * @param string $line
     * @param array $commitLinesByPrNumber
     * @param string $headlineLine
     */
    private function replaceProjectBaseDiffHashWithLinks(
        array &$fileLines,
        int $lineNumber,
        string $line,
        array $commitLinesByPrNumber,
        string $headlineLine,
    ): void {
        $links = [];

        if (count($commitLinesByPrNumber) === 0) {
            $this->symfonyStyle->warning('Headline ' . $headlineLine . ' has incorrect format please fix it.');

            return;
        }

        foreach ($commitLinesByPrNumber as $prNumber => $commitLines) {
            if ($commitLines === null) {
                $this->symfonyStyle->warning('For PR #' . $prNumber . ' has been found #project-base-diff hash but no corresponding commit.');

                $commitSha = $this->symfonyStyle->ask('Enter commit SHA1 from project-base repository for PR #' . $prNumber);
            } elseif (count($commitLines) === 1) {
                $commitSha = explode(' ', $commitLines[0])[0];
            } else {
                $this->symfonyStyle->warning('For PR #' . $prNumber . ' has been found multiple possible commits:');

                foreach ($commitLines as $commitLine) {
                    $this->symfonyStyle->note($commitLine);
                }

                $commitSha = $this->symfonyStyle->ask('Enter commit SHA1 from project-base repository for PR #' . $prNumber);
            }

            $links[] = self::PROJECT_BASE_DIFF_LINK_TEXT . '(https://www.github.com/shopsys/project-base/commit/' . $commitSha . ')';
        }

        if (count($links) > 0) {
            $fileLines[$lineNumber] = str_replace('#project-base-diff', implode(' and ', $links), $line);
        } else {
            $this->symfonyStyle->warning('For PR ' . implode(' and ', array_keys($commitLinesByPrNumber)) . ' has been found #project-base-diff tag but no relevant commit.');
        }
    }

    /**
     * @param array $fileLines
     * @param int $fromLineNumber
     */
    private function increaseLineNumberFrom(array &$fileLines, int $fromLineNumber): void
    {
        foreach ($fileLines as $lineNumber => $line) {
            if ($lineNumber >= $fromLineNumber) {
                $fileLines[$lineNumber + 1] = $line;
            }
        }
    }

    /**
     * @param array $headlineLines
     * @param int $headlineLineNumber
     * @return int|null
     */
    private function getNextHeadlineLineNumber(array $headlineLines, int $headlineLineNumber): int|null
    {
        $headlineLineNumbers = array_keys($headlineLines);
        $indexOfCurrentHeadlineLine = array_search($headlineLineNumber, $headlineLineNumbers, true);

        return $headlineLineNumbers[$indexOfCurrentHeadlineLine + 1] ?? null;
    }

    /**
     * @param array $headlineLines
     * @param int $headlineLineNumber
     * @return int|null
     */
    private function getPreviousHeadlineLineNumber(array $headlineLines, int $headlineLineNumber): int|null
    {
        $headlineLineNumbers = array_keys($headlineLines);
        $indexOfCurrentHeadlineLine = array_search($headlineLineNumber, $headlineLineNumbers, true);

        return $headlineLineNumbers[$indexOfCurrentHeadlineLine - 1] ?? null;
    }

    /**
     * @param array $fileLines
     * @param array $headlineLines
     * @return array
     */
    private function addLastLineAsHeadlineToEnsureLastUpgradeNoteIsProcessed(
        array $fileLines,
        array $headlineLines,
    ): array {
        $lastLineNumber = array_key_last($fileLines);
        $headlineLines[$lastLineNumber] = $fileLines[$lastLineNumber];

        return $headlineLines;
    }
}
