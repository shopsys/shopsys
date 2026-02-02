<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\DomainDbFunctionsFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:domains-db-functions:create',
    description: 'Create new domains DB functions',
)]
class CreateDomainsDbFunctionsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DomainDbFunctionsFacade $domainDbFunctionsFacade,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->wrapInTransaction(function () use ($output): void {
            $this->doExecute($output);
        });

        return Command::SUCCESS;
    }

    private function doExecute(OutputInterface $output): void
    {
        $output->writeln('Start of creating db functions.');

        $this->domainDbFunctionsFacade->createDomainDbFunctions();

        $output->writeln('<fg=green>All db functions created.</fg=green>');
    }
}
