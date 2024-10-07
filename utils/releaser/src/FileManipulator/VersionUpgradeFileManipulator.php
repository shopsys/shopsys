<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;
use PharIo\Version\Version;

final class VersionUpgradeFileManipulator
{
    /**
     * @var string
     * @see https://regex101.com/r/HArQ3c/1
     */
    private const string HEADLINE_WITH_LINK_PATTERN = '#(\#\# \[Upgrade from [\w.-]+ to [\w.-]+\]\(.+\))#';

    private const string HEADLINE_TEMPLATE = '## [Upgrade from %s to %s](https://github.com/shopsys/shopsys/compare/%s...%s)' . PHP_EOL . PHP_EOL . '$1';

    /**
     * @param string $fileContent
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function processFileToString(string $fileContent, Version $version, string $initialBranchName): string
    {
        $content = $this->updateHeadline($version, $fileContent, $initialBranchName);

        return $this->addNewPatchHeadline($version, $content, $initialBranchName);
    }

    /**
     * Before:
     * ## [Upgrade from v12.0.0 to v12.1.0-dev](https://github.com/shopsys/shopsys/compare/v12.0.0...12.1)
     *
     * After:
     * ## [Upgrade from v12.0.0 to v12.1.0](https://github.com/shopsys/shopsys/compare/v12.0.0...v12.1.0)
     *
     * @param \PharIo\Version\Version $version
     * @param string $content
     * @param string $initialBranchName
     * @return string
     */
    public function updateHeadline(Version $version, string $content, string $initialBranchName): string
    {
        $versionString = $version->getOriginalString();

        return Strings::replace(
            $content,
            self::HEADLINE_WITH_LINK_PATTERN,
            static function ($match) use ($versionString, $initialBranchName) {
                return str_replace(
                    [$versionString . '-dev', '...' . $initialBranchName],
                    [$versionString, '...' . $versionString],
                    $match[0],
                );
            },
        );
    }

    /**
     * Before:
     * ## [Upgrade from v12.0.0 to v12.1.0](https://github.com/shopsys/shopsys/compare/v12.0.0...v12.1.0)
     *
     * After:
     * ## [Upgrade from v12.1.0 to v12.1.1-dev](https://github.com/shopsys/shopsys/compare/v12.0.0...12.1)
     *
     * ## [Upgrade from v12.0.0 to v12.1.0](https://github.com/shopsys/shopsys/compare/v12.0.0...v12.1.0)
     *
     * @param \PharIo\Version\Version $version
     * @param string $content
     * @param string $initialBranchName
     * @return string
     */
    private function addNewPatchHeadline(Version $version, string $content, string $initialBranchName): string
    {
        $nextPatchVersionString = 'v' . $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue() . '.' . ($version->getPatch()->getValue() + 1);
        $nextPatchVersion = new Version($nextPatchVersionString);

        return Strings::replace(
            $content,
            self::HEADLINE_WITH_LINK_PATTERN,
            sprintf(
                self::HEADLINE_TEMPLATE,
                $version->getOriginalString(),
                $nextPatchVersion->getOriginalString() . '-dev',
                $version->getOriginalString(),
                $initialBranchName,
            ),
            1,
        );
    }
}
