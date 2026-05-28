<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Nette\Utils\Strings;
use Override;
use PharIo\Version\Version;
use RuntimeException;
use Shopsys\Releaser\Command\SymfonyStyleFactory;
use Shopsys\Releaser\GithubActions\GithubTokenProvider;
use Shopsys\Releaser\Process\ProcessRunner;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractShopsysReleaseWorker implements StageWorkerInterface
{
    public const string MAIN_BRANCH_NAME = 'master';

    public const string ORGANIZATION = 'shopsys';

    public const string MONOREPO_REPOSITORY = 'shopsys';

    public const string PHP_IMAGE_PACKAGE_NAME = 'php-image';

    private const int MAX_WAIT_SECONDS = 7200;

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
        'shopsys/convertim',
        'shopsys/read-model',
        // forks
        'shopsys/postgres-search-bundle',
        'shopsys/doctrine-orm',
        'shopsys/jparser',
        'shopsys/ordered-form',
        'shopsys/changelog-linker',
        'shopsys/jsformvalidator-bundle',
        'shopsys/phpunit-injector',
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

    protected int $currentStep = 0;

    protected GithubTokenProvider $githubTokenProvider;

    #[Override]
    public function setCurrentStep(int $currentStep): void
    {
        $this->currentStep = $currentStep;
    }

    /**
     * @throws \Shopsys\Releaser\Exception\ShouldNotHappenException
     */
    #[Required]
    public function setup(
        SymfonyStyleFactory $symfonyStyleFactory,
        ProcessRunner $processRunner,
        GithubTokenProvider $githubTokenProvider,
    ): void {
        $this->symfonyStyle = $symfonyStyleFactory->getPreviouslyCreatedSymfonyStyle();
        $this->processRunner = $processRunner;
        $this->githubTokenProvider = $githubTokenProvider;
        $this->currentBranchName = $this->processRunner->run('git rev-parse --abbrev-ref HEAD');
    }

    /**
     * Question helper modifications that only waits for "enter"
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

    protected function waitFor(WaitForExternalConditionInterface $condition): void
    {
        $description = $condition->describe();
        $intervalSeconds = $condition->pollIntervalSeconds();
        $maxAttempts = (int)ceil(self::MAX_WAIT_SECONDS / $intervalSeconds);

        $this->symfonyStyle->note(sprintf('Waiting for: %s', $description));

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            if ($condition->check()) {
                $this->symfonyStyle->success(sprintf('Condition met: %s', $description));

                return;
            }

            if ($attempt === $maxAttempts) {
                break;
            }

            $this->symfonyStyle->writeln(sprintf(
                'attempt %d: %s; sleeping %ds',
                $attempt,
                $condition->progressDescription(),
                $intervalSeconds,
            ));
            sleep($intervalSeconds);
        }

        $this->symfonyStyle->warning(sprintf(
            'Gave up after %d attempts waiting for: %s',
            $maxAttempts,
            $description,
        ));
        $this->confirm(sprintf('Continue when "%s" is satisfied', $description));
    }

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

    protected function createBranchName(Version $version): string
    {
        return 'rc-' . Strings::webalize($version->getVersionString());
    }

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
     */
    protected function suggestDevelopmentVersion(Version $version, bool $suggestWithVprefix = false): Version
    {
        $newVersionString = $version->getMajor()->getValue() . '.' . ($version->getMinor()->getValue() + 1) . '.0-dev';

        if ($suggestWithVprefix) {
            $newVersionString = 'v' . $newVersionString;
        }

        return new Version($newVersionString);
    }

    #[Override]
    public function belongToStage(string $stage): bool
    {
        return in_array($stage, $this->getAllowedStages(), true);
    }

    protected function resolveGithubToken(): string
    {
        return $this->githubTokenProvider->getToken();
    }

    /**
     * @return string[]
     */
    abstract protected function getAllowedStages(): array;
}
