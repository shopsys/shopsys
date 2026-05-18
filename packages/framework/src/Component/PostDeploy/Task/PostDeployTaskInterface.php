<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Symfony\Component\Console\Style\SymfonyStyle;

interface PostDeployTaskInterface
{
    public function run(SymfonyStyle $style): void;
}
