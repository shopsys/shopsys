<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\FileSystem;

final class DockerfileVersionFileManipulator
{
    /**
     * @param $versionString
     */
    public function updateDockerFileVersion(
        $versionString,
    ): void {
        $dockerFilePath = getcwd() . '/project-base/app/docker/php-fpm/Dockerfile';
        $fileContent = FileSystem::read($dockerFilePath);

        $replacement = ':' . $versionString . ' as base';
        $newContent = preg_replace('/:([\w.-]+) as base/', $replacement, $fileContent);

        FileSystem::write($dockerFilePath, $newContent);
    }
}
