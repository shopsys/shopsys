<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\FileSystem;

final class DockerfileVersionFileManipulator
{
    public function updateDockerFileVersion(
        $versionString,
    ): void {
        $dockerFilePath = getcwd() . '/project-base/app/docker/php-fpm/Dockerfile';
        $fileContent = FileSystem::read($dockerFilePath);

        $replacement = ':' . $versionString . ' AS base';
        $newContent = preg_replace('/:([\w.-]+) AS base/', $replacement, $fileContent);

        FileSystem::write($dockerFilePath, $newContent);
    }
}
