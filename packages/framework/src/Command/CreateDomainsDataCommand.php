<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Domain\DomainDataCreator;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade;
use Shopsys\FrameworkBundle\Model\Localization\DbIndexesFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'shopsys:domains-data:create')]
class CreateDomainsDataCommand extends Command
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainDataCreator $domainDataCreator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade
     * @param \Shopsys\FrameworkBundle\Model\Localization\DbIndexesFacade $dbIndexesFacade
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DomainDataCreator $domainDataCreator,
        private readonly MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade,
        private readonly DbIndexesFacade $dbIndexesFacade,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create new domains data');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domainsCreatedCount = 0;
        $this->em->wrapInTransaction(function () use ($output, &$domainsCreatedCount): void {
            $domainsCreatedCount = $this->doExecute($output);
        });

        if ($domainsCreatedCount > 0) {
            $application = $this->getApplicationInstance();
            $recalculationsCommand = $application->get(RecalculationsCommand::getDefaultName());

            return $recalculationsCommand->run($input, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    private function doExecute(OutputInterface $output): int
    {
        $output->writeln('Start of creating new domains data.');

        $domainsCreatedCount = $this->domainDataCreator->createNewDomainsData();

        $output->writeln('<fg=green>New domains created: ' . $domainsCreatedCount . '.</fg=green>');

        $multidomainEntitiesNames = $this->multidomainEntityClassFinderFacade->getMultidomainEntitiesNames();
        $output->writeln('<fg=green>Multidomain entities found:</fg=green>');

        foreach ($multidomainEntitiesNames as $multidomainEntityName) {
            $output->writeln($multidomainEntityName);
        }
        $this->dbIndexesFacade->updateLocaleSpecificIndexes();
        $output->writeln('<fg=green>All locale specific db indexes updated.</fg=green>');

        return $domainsCreatedCount;
    }

    /**
     * @return \Symfony\Component\Console\Application
     */
    protected function getApplicationInstance(): \Symfony\Component\Console\Application
    {
        $application = $this->getApplication();

        if ($application !== null) {
            return $application;
        }

        throw new RuntimeException('Application must be loaded.');
    }
}
