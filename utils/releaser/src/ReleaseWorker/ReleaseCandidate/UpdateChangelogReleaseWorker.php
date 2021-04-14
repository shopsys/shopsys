<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symplify\MonorepoBuilder\Split\Git\GitManager;

final class UpdateChangelogReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const GITHUB_COMPANY_NAME = 'shopsys';
    private const GITHUB_PROJECT_NAME = 'shopsys';

    /**
     * @var \Symplify\MonorepoBuilder\Split\Git\GitManager
     */
    private $gitManager;

    /**
     * @param \Symplify\MonorepoBuilder\Split\Git\GitManager $gitManager
     */
    public function __construct(GitManager $gitManager)
    {
        $this->gitManager = $gitManager;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Dump new features to CHANGELOG.md, clean from placeholders and [Manually] check everything is ok';
    }

    /**
     * Higher first
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 820;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('It is necessary to set Github token before the changelog content is generated');
        $githubToken = $this->symfonyStyle->ask(
            'Please enter no-scope Github token (https://github.com/settings/tokens/new)'
        );

        $mostRecentVersion = new Version($this->gitManager->getMostRecentTag(getcwd()));

        $this->symfonyStyle->note('In order to generate new changelog entries you need to run this command outside of container:');
        $this->symfonyStyle->write(
            sprintf(
                'docker run -it --rm -v "$(pwd)":/usr/local/src/your-app ferrarimarco/github-changelog-generator github_changelog_generator -u %s -p %s --token %s --base CHANGELOG.md --no-issues --since-tag %s --future-release %s --no-filter-by-milestone --configure-sections \'%s\'',
                self::GITHUB_COMPANY_NAME,
                self::GITHUB_PROJECT_NAME,
                $githubToken,
                $mostRecentVersion->getVersionString(),
                $version->getVersionString(),
                $this->getSectionsDefinition(),
            )
        );

        $this->symfonyStyle->note(
            sprintf(
                'You need to review the file, resolve unclassified entries, remove uninteresting entries, and commit the changes manually with "changelog is now updated for %s release"',
                $version->getVersionString()
            )
        );

        $this->confirm('Confirm you have checked CHANGELOG.md and the changes are committed.');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }

    /**
     * @return string
     */
    private function getSectionsDefinition(): string
    {
        $sectionsDefinition = [
            'enhancements' => [
                'prefix' => ':sparkles: Enhancements and features',
                'labels' => [
                    'Enhancement',
                ],
            ],
            'bugs' => [
                'prefix' => ':bug: Bug fixes',
                'labels' => [
                    'Bug',
                ],
            ],
            'refactor' => [
                'prefix' => ':hammer: Developer experience and refactoring',
                'labels' => [
                    'DX & Refactoring',
                ],
            ],
            'docs' => [
                'prefix' => ':book: Documentation',
                'labels' => [
                    'Documentation',
                ],
            ],
            'design' => [
                'prefix' => ':art: Design & appearance',
                'labels' => [
                    'Design & Appearance',
                ],
            ],
            'performance' => [
                'prefix' => ':rocket: Performance',
                'labels' => [
                    'Performance',
                ],
            ],
            'infrastructure' => [
                'prefix' => ':cloud: Infrastructure',
                'labels' => [
                    'Infrastructure',
                ],
            ],
            'security' => [
                'prefix' => ':warning: Security',
                'labels' => [
                    'Security',
                ],
            ],
        ];

        return json_encode($sectionsDefinition);
    }
}
