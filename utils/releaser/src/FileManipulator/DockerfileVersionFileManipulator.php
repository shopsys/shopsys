<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\FileSystem;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DockerfileVersionFileManipulator
{
    /**
     * @param $versionString
     */
    public function updateDockerFileVersion(
        $versionString,
    ): void {
        $dockerFilePath = getcwd() . '/project-base/app/docker/php-fpm/Dockerfile';
        $smartFileInfo = new SmartFileInfo($dockerFilePath);
        $fileContent = $smartFileInfo->getContents();

        $replacement = ':' . $versionString . ' as base';
        $newContent = preg_replace('/:([\w.-]+) as base/', $replacement, $fileContent);

        FileSystem::write($dockerFilePath, $newContent);
    }
}
