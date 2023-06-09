<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Symfony\Component\Finder\SplFileInfo;

final class VersionUpgradeFileManipulator
{
    /**
     * @var string
     * @see https://regex101.com/r/izBgtv/7
     */
    private const HEADLINE_WITH_LINK_PATTERN = '#\# \[Upgrade from [\w.-]+ to [\w.-]+\]\(.+\)#';

    /**
     * @var string
     */
    private const FILE_CONTENT_INFORMATION_PATTERN = '#This guide contains instructions to upgrade from version .* to .*-dev#';

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $splFileInfo
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function processFileToString(SplFileInfo $splFileInfo, Version $version, string $initialBranchName): string
    {
        $content = $this->updateHeadline($version, $splFileInfo->getContents(), $initialBranchName);

        return $this->updateFileContentInformation($version, $content);
    }

    /**
     * Before:
     * # [Upgrade from v0.9.0 to v1.0.0-dev](https://github.com/shopsys/shopsys/compare/v0.9.0...1.0)
     *
     * After:
     * # [Upgrade from v0.9.0 to v1.0.0](https://github.com/shopsys/shopsys/compare/v0.9.0...v1.0.0)
     *
     * @param \PharIo\Version\Version $version
     * @param string $content
     * @param string $initialBranchName
     * @return string
     */
    private function updateHeadline(Version $version, string $content, string $initialBranchName): string
    {
        $versionString = $version->getOriginalString();
        return Strings::replace(
            $content,
            self::HEADLINE_WITH_LINK_PATTERN,
            function ($match) use ($versionString, $initialBranchName) {
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
     * This guide contains instructions to upgrade from version v0.9.0 to v1.0.0-dev
     *
     * After:
     * This guide contains instructions to upgrade from version v0.9.0 to v1.0.0
     *
     * @param \PharIo\Version\Version $version
     * @param string $content
     * @return string
     */
    private function updateFileContentInformation(Version $version, string $content): string
    {
        $versionString = $version->getOriginalString();
        return Strings::replace(
            $content,
            self::FILE_CONTENT_INFORMATION_PATTERN,
            function ($match) use ($versionString) {
                return str_replace($versionString . '-dev', $versionString, $match[0]);
            },
        );
    }
}
