<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

abstract class AbstractShopsysReleaseWorker implements ReleaseWorkerInterface, StageAwareInterface
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $symfonyStyle;

    /**
     * @var \Symplify\MonorepoBuilder\Release\Process\ProcessRunner
     */
    protected $processRunner;

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper
     */
    private $questionHelper;

    /**
     * @var string
     */
    protected $initialBranchName;

    /**
     * @required
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Symplify\MonorepoBuilder\Release\Process\ProcessRunner $processRunner
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     */
    public function setup(SymfonyStyle $symfonyStyle, ProcessRunner $processRunner, QuestionHelper $questionHelper): void
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->processRunner = $processRunner;
        $this->questionHelper = $questionHelper;
        $this->initialBranchName = $this->getProcessResult(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
    }

    /**
     * Question helper modifications that only waits for "enter"
     * @param string $message
     */
    protected function confirm(string $message): void
    {
        $this->questionHelper->ask(
            new ArgvInput(),
            new ConsoleOutput(),
            new Question(' <info>' . $message . '</info> [<comment>Enter</comment>]')
        );
    }

    /**
     * Check if there are some changes and if so, add them and commit them
     * @param string $message
     */
    protected function commit(string $message): void
    {
        if ($this->hasChangesToCommit() === false) {
            return;
        }

        $this->configureGitIdentityIfMissing();

        $this->processRunner->run('git add .');
        $this->processRunner->run('git commit --message="' . addslashes($message) . '"');
    }

    /**
     * @return bool
     */
    private function hasChangesToCommit(): bool
    {
        $process = new Process(['git', 'status', '-s']);
        $process->run();

        $output = $process->getOutput();

        return !(bool)empty($output);
    }

    private function configureGitIdentityIfMissing(): void
    {
        $name = $this->getProcessResult(['git', 'config', 'user.name']);
        $email = $this->getProcessResult(['git', 'config', 'user.email']);

        if ($name === '' || $email === '') {
            $this->symfonyStyle->warning('Git identity is not configured, unable to create commits...');
        }

        if ($name === '') {
            $newName = $this->symfonyStyle->ask('What is your name?');
            $this->processRunner->run(['git', 'config', 'user.name', $newName]);
        }

        if ($email === '') {
            $newEmail = $this->symfonyStyle->ask('What is your email address?');
            $this->processRunner->run(['git', 'config', 'user.email', $newEmail]);
        }
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    protected function createBranchName(Version $version): string
    {
        return 'rc-' . Strings::webalize($version->getVersionString());
    }

    /**
     * @return bool
     */
    protected function isGitWorkingTreeEmpty(): bool
    {
        $status = $this->getProcessResult(['git', 'status']);

        return Strings::contains($status, 'nothing to commit');
    }

    /**
     * @param string[] $commandLine
     * @return string
     */
    protected function getProcessResult(array $commandLine): string
    {
        $process = new Process($commandLine);
        $process->run();

        return trim($process->getOutput());
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param bool $suggestWithVprefix
     * @return \PharIo\Version\Version
     */
    protected function askForNextDevelopmentVersion(Version $version, bool $suggestWithVprefix = false): Version
    {
        $suggestedDevelopmentVersion = $this->suggestDevelopmentVersion($version, $suggestWithVprefix);

        $question = new Question('Enter next development version of Shopsys Framework', $suggestedDevelopmentVersion->getVersionString());
        $question->setValidator(static function ($answer) {
            $version = new Version($answer);

            if (!$version->hasPreReleaseSuffix()) {
                throw new RuntimeException(
                    'Development version must be suffixed (with \'-dev\', \'-alpha1\', ...)'
                );
            }

            return $version;
        });

        return $this->symfonyStyle->askQuestion($question);
    }

    /**
     * Return new development version (e.g. from 7.1.0 to 7.2.0-dev)
     * @param \PharIo\Version\Version $version
     * @param bool $suggestWithVprefix
     * @return \PharIo\Version\Version
     */
    protected function suggestDevelopmentVersion(Version $version, bool $suggestWithVprefix = false): Version
    {
        $newVersionString = $version->getMajor()->getValue() . '.' . ($version->getMinor()->getValue() + 1) . '.0-dev';
        if ($suggestWithVprefix) {
            $newVersionString = 'v' . $newVersionString;
        }

        return new Version($newVersionString);
    }
}
