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
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

abstract class AbstractShopsysReleaseWorker implements StageWorkerInterface
{
    public const MAIN_BRANCH_NAME = 'master';

    public const PHP_IMAGE_PACKAGE_NAME = 'php-image';

    /**
     * If you modify this list do not forget updating:
     *      /.ci/monorepo_functions.sh
     *      /docs/introduction/monorepo.md
     *      /CHANGELOG-XX.X.md
     *      "replace" section in monorepo's composer.json as well
     *
     * @var string[]
     */
    public const EXCLUDED_PACKAGES = [
        // not maintained anymore
        'shopsys/product-feed-interface',
        'shopsys/phpstorm-inspect',
        'shopsys/changelog-linker',
        'shopsys/monorepo-builder',
        'shopsys/backend-api',
        'shopsys/read-model',
        // used in newer versions
        'shopsys/administration',
        // forks
        'shopsys/postgres-search-bundle',
        'shopsys/doctrine-orm',
        'shopsys/jparser',
        'shopsys/ordered-form',
        'shopsys/changelog-linker',
        'shopsys/jsformvalidator-bundle',
        // packages outside monorepo
        'shopsys/deployment',
        // not related packages
        'shopsys/syscart',
        'shopsys/sysconfig',
        'shopsys/sysreports',
        'shopsys/sysstdlib',
    ];

    protected SymfonyStyle $symfonyStyle;

    protected ProcessRunner $processRunner;

    private QuestionHelper $questionHelper;

    protected string $currentBranchName;

    /**
     * @required
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Symplify\MonorepoBuilder\Release\Process\ProcessRunner $processRunner
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     */
    public function setup(
        SymfonyStyle $symfonyStyle,
        ProcessRunner $processRunner,
        QuestionHelper $questionHelper,
    ): void {
        $this->symfonyStyle = $symfonyStyle;
        $this->processRunner = $processRunner;
        $this->questionHelper = $questionHelper;
        $this->currentBranchName = $this->getProcessResult(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
    }

    /**
     * Question helper modifications that only waits for "enter"
     *
     * @param string $message
     */
    protected function confirm(string $message): void
    {
        $this->questionHelper->ask(
            new ArgvInput(),
            new ConsoleOutput(),
            new Question(' <info>' . $message . '</info> [<comment>Enter</comment>]'),
        );
    }

    /**
     * Check if there are some changes and if so, add them and commit them
     *
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

        return $output !== '';
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

        if ($email !== '') {
            return;
        }

        $newEmail = $this->symfonyStyle->ask('What is your email address?');
        $this->processRunner->run(['git', 'config', 'user.email', $newEmail]);
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

        $question = new Question(
            'Enter next development version of Shopsys Platform',
            $suggestedDevelopmentVersion->getOriginalString(),
        );
        $question->setValidator(static function ($answer) {
            $version = new Version($answer);

            if (!str_starts_with($version->getOriginalString(), 'v')) {
                throw new RuntimeException(
                    'Development version name must start with \'v\'',
                );
            }

            if (!$version->hasPreReleaseSuffix()) {
                throw new RuntimeException(
                    'Development version must be suffixed (with \'-dev\', \'-alpha1\', ...)',
                );
            }

            return $version;
        });

        return $this->symfonyStyle->askQuestion($question);
    }

    /**
     * Return new development version (e.g. from 7.1.0 to 7.2.0-dev)
     *
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
