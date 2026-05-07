<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskRunnerFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:post-deploy:run-tasks',
    description: 'Runs post-deploy tasks declared in app/config/post_deploy_tasks.yaml (one_time tasks are recorded and skipped on subsequent runs).',
)]
class RunPostDeployTasksCommand extends Command
{
    public function __construct(
        protected readonly PostDeployTaskRunnerFacade $postDeployTaskRunnerFacade,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $this->postDeployTaskRunnerFacade->run($style);

        return Command::SUCCESS;
    }
}
