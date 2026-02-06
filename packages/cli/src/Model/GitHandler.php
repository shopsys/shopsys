<?php

declare(strict_types=1);

namespace Shopsys\Cli\Model;

use Shopsys\Cli\Exception\GitException;
use Symfony\Component\Process\Process;

final class GitHandler
{
    public function cloneRepository(
        string $repositoryUrl,
        string $targetDirectory,
        string $branch,
        ?callable $outputCallback = null,
    ): void {
        $process = new Process([
            'git',
            'clone',
            '--branch',
            $branch,
            $repositoryUrl,
            $targetDirectory,
        ]);

        $process->setTimeout(3000);

        $process->run($outputCallback);

        if (!$process->isSuccessful()) {
            throw new GitException(
                sprintf(
                    'Failed to clone repository: %s',
                    $process->getErrorOutput(),
                ),
            );
        }

        $this->removeRemoteOrigin($targetDirectory, $outputCallback);
    }

    public function getLatestTag(string $repositoryUrl): string
    {
        $process = new Process([
            'git',
            'ls-remote',
            '--tags',
            '--refs',
            '--sort=-v:refname',
            $repositoryUrl,
        ]);

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException(
                sprintf(
                    'Failed to fetch tags from repository: %s',
                    $process->getErrorOutput(),
                ),
            );
        }

        $output = trim($process->getOutput());

        if ($output === '') {
            throw new GitException('No tags found in the repository');
        }

        $lines = explode("\n", $output);
        $firstLine = $lines[0];
        preg_match('/refs\/tags\/(.+)$/', $firstLine, $matches);

        if (!isset($matches[1])) {
            throw new GitException('Failed to parse tag name from git output');
        }

        return $matches[1];
    }

    private function removeRemoteOrigin(string $targetDirectory, ?callable $outputCallback): void
    {
        $process = new Process(
            [
                'git',
                'remote',
                'remove',
                'origin',
            ],
            $targetDirectory,
        );

        $process->setTimeout(60);
        $process->run($outputCallback);

        if (!$process->isSuccessful()) {
            throw new GitException(
                sprintf(
                    'Failed to remove remote origin: %s',
                    $process->getErrorOutput(),
                ),
            );
        }
    }
}
