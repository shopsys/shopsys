<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @see https://github.com/shopsys/shopsys/pull/470/commits/f92e5fe531be771323ee142579117b47bfac1d4e
 */
final class MonorepoUpgradeFileManipulator
{
    /**
     * @var string
     * @see https://regex101.com/r/cHAbva/4
     */
    private const FROM_TO_NEXT_DEV_PATTERN = '#^(?<start>\#\# \[?From [\w.-]+ to )[\w.-]+-dev(?<end>]?)$#m';

    /**
     * @var string
     * @see https://regex101.com/r/izBgtv/8
     */
    private const FROM_TO_NEXT_DEV_LINK_PATTERN = '#^(?<start>\[From [\w.-]+ to )[\w.-]+-dev(?<middle>.*?\.\.\.).*?\n#m';

    /**
     * @var string
     */
    private $monorepoPackageName;

    /**
     * @param string $monorepoPackageName
     */
    public function __construct(string $monorepoPackageName)
    {
        $this->monorepoPackageName = $monorepoPackageName;
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $splFileInfo
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @param string $nextDevelopmentVersionString
     * @return string
     */
    public function processFileToString(SplFileInfo $splFileInfo, Version $version, string $initialBranchName, string $nextDevelopmentVersionString): string
    {
        $content = $this->updateHeadlines($version, $splFileInfo->getContents(), $nextDevelopmentVersionString);

        return $this->updateFooterLinks($version, $content, $initialBranchName, $nextDevelopmentVersionString);
    }

    /**
     * Before:
     * ## [From v0.9.0 to v1.0.0-dev]
     *
     * After:
     * ## [From v1.0.0 to v1.0.1-dev]
     *
     * ## [From v0.9.0 to v1.0.0]
     *
     * @param \PharIo\Version\Version $version
     * @param string $content
     * @param string $nextDevelopmentVersionString
     * @return string
     */
    private function updateHeadlines(Version $version, string $content, string $nextDevelopmentVersionString): string
    {
        $newHeadline = $this->createNewHeadline($version, $nextDevelopmentVersionString);

        // already done
        if (Strings::contains($content, $newHeadline)) {
            return $content;
        }

        return Strings::replace(
            $content,
            self::FROM_TO_NEXT_DEV_PATTERN,
            function ($match) use ($version, $newHeadline) {
                return $newHeadline . $match['start'] . $version->getVersionString() . $match['end'];
            }
        );
    }

    /**
     * Before:
     * [From v0.9.0 to v1.0.0-dev]: https://github.com/shopsys/shopsys/compare/v0.9.0...1.0
     *
     * After:
     * [From v1.0.0 to v1.0.1-dev]: https://github.com/shopsys/shopsys/compare/v1.0.0...1.0
     * [From v0.9.0 to v1.0.0]: https://github.com/shopsys/shopsys/compare/v0.9.0...v1.0.0
     *
     * @param \PharIo\Version\Version $version
     * @param string $content
     * @param string $initialBranchName
     * @param string $nextDevelopmentVersionString
     * @return string
     */
    private function updateFooterLinks(Version $version, string $content, string $initialBranchName, string $nextDevelopmentVersionString): string
    {
        $newFooterLink = $this->createNewFooterLink($version, $initialBranchName, $nextDevelopmentVersionString);

        // already done
        if (Strings::contains($content, $newFooterLink)) {
            return $content;
        }

        return Strings::replace(
            $content,
            self::FROM_TO_NEXT_DEV_LINK_PATTERN,
            function (array $match) use ($newFooterLink, $version) {
                return $newFooterLink . $match['start'] . $version->getVersionString() . $match['middle'] . $version->getVersionString() . PHP_EOL;
            }
        );
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $nextDevelopmentVersionString
     * @return string
     */
    private function createNewHeadline(Version $version, string $nextDevelopmentVersionString): string
    {
        return sprintf('## [From %s to %s]' . PHP_EOL . PHP_EOL, $version->getVersionString(), $nextDevelopmentVersionString);
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @param string $nextDevelopmentVersionString
     * @return string
     */
    private function createNewFooterLink(Version $version, string $initialBranchName, string $nextDevelopmentVersionString): string
    {
        return sprintf(
            '[From %s to %s]: https://github.com/%s/compare/%s...%s' . PHP_EOL,
            $version->getVersionString(),
            $nextDevelopmentVersionString,
            $this->monorepoPackageName,
            $version->getVersionString(),
            $initialBranchName
        );
    }
}
