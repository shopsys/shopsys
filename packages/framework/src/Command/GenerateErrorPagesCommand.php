<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:error-page:generate-all',
    description: 'Generates all error pages for production',
)]
class GenerateErrorPagesCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
     */
    public function __construct(private readonly ErrorPagesFacade $errorPagesFacade)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->errorPagesFacade->generateAllErrorPagesForProduction();

        return Command::SUCCESS;
    }
}
