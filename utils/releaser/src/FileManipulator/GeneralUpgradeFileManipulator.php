<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Symfony\Component\Finder\SplFileInfo;

class GeneralUpgradeFileManipulator
{
    private const FROM_PREVIOUS_TO_NEXT_DEV_LINK_PATTERN = '#^\* \#\#\# \[From [\w.-]+ to [\w.-]+-dev\]\(\.\/upgrade\/UPGRADE-[\w.-]+-dev\.md\)$#m';

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $splFileInfo
     * @param \PharIo\Version\Version $version
     * @param string $nextDevelopmentVersionString
     * @return string
     */
    public function updateLinks(SplFileInfo $splFileInfo, Version $version, string $nextDevelopmentVersionString): string
    {
        $newLink = sprintf('* ### [From %s to %2$s](./upgrade/UPGRADE-%2$s.md)' . PHP_EOL, $version->getVersionString(), $nextDevelopmentVersionString);
        $versionString = $version->getVersionString();
        return Strings::replace(
            $splFileInfo->getContents(),
            self::FROM_PREVIOUS_TO_NEXT_DEV_LINK_PATTERN,
            function ($match) use ($versionString, $newLink) {
                return $newLink . str_ireplace($versionString . '-dev', $versionString, $match[0]);
            }
        );
    }
}
