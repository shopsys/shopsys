<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use RuntimeException;
use Shopsys\Releaser\Command\SymfonyStyleFactory;
use Shopsys\Releaser\Process\ProcessRunner;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractShopsysReleaseWorker implements StageWorkerInterface
{
    public const string MAIN_BRANCH_NAME = 'master';

    public const string PHP_IMAGE_PACKAGE_NAME = 'php-image';

    /**
     * If you modify this list, do not forget updating:
     *      /.github/monorepo/monorepo_functions.sh
     *      /docs/introduction/monorepo.md
     *      /CHANGELOG-XX.X.md
     *      /packages/framework/src/Resources/config/packages_registry.yaml
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

    protected string $currentBranchName;

    /**
     * @required
     * @param \Shopsys\Releaser\Command\SymfonyStyleFactory $symfonyStyleFactory
     * @param \Shopsys\Releaser\Process\ProcessRunner $processRunner
     * @throws \Shopsys\Releaser\Exception\ShouldNotHappenException
     */
    public function setup(
        SymfonyStyleFactory $symfonyStyleFactory,
        ProcessRunner $processRunner,
    ): void {
        $this->symfonyStyle = $symfonyStyleFactory->getPreviouslyCreatedSymfonyStyle();
        $this->processRunner = $processRunner;
        $this->currentBranchName = $this->processRunner->run('git rev-parse --abbrev-ref HEAD');
    }

    /**
     * Question helper modifications that only waits for "enter"
     *
     * @param string $message
     */
    protected function confirm(string $message): void
    {
        $this->symfonyStyle->askQuestion(
            new Question(' <info>' . $message . '</info> [<comment>Enter</comment>]'),
        );
    }

    protected function success(): void
    {
        $this->symfonyStyle->success('All good!');
    }

    /**
     * Check if there are some changes and if so, add them and commit them
     *
     * @param string $message
     */
    protected function commit(string $message): void
    {
        if ($this->isGitWorkingTreeEmpty()) {
            return;
        }

        $this->configureGitIdentityIfMissing();

        $this->processRunner->run('git add .');
        $this->processRunner->run('git commit --message="' . addslashes($message) . '"');
    }

    /**
     * @return bool
     */
    protected function isGitWorkingTreeEmpty(): bool
    {
        return $this->processRunner->run('git status --porcelain') === '';
    }

    private function configureGitIdentityIfMissing(): void
    {
        $name = $this->processRunner->run('git config user.name');
        $email = $this->processRunner->run('git config user.email');

        if ($name === '' || $email === '') {
            $this->symfonyStyle->warning('Git identity is not configured, unable to create commits...');
        }

        if ($name === '') {
            $newName = $this->symfonyStyle->ask('What is your name?');
            $this->processRunner->run(sprintf('git config user.name "%s"', addslashes($newName)));
        }

        if ($email !== '') {
            return;
        }

        $newEmail = $this->symfonyStyle->ask('What is your email address?');
        $this->processRunner->run(sprintf('git config user.email "%s"', addslashes($newEmail)));
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

    /**
     * @param string $stage
     * @return bool
     */
    public function belongToStage(string $stage): bool
    {
        return in_array($stage, $this->getAllowedStages(), true);
    }

    /**
     * @return string[]
     */
    abstract protected function getAllowedStages(): array;
}
