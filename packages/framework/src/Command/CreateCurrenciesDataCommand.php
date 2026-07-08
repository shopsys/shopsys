<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataCreator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:currencies-data:create',
    description: 'Create currencies configured in domains.yaml that are missing in the database',
)]
class CreateCurrenciesDataCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CurrencyDataCreator $currencyDataCreator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $createdCurrenciesCount = 0;

        $this->em->wrapInTransaction(function () use (&$createdCurrenciesCount): void {
            $createdCurrenciesCount = $this->currencyDataCreator->createMissingCurrencies();
        });

        $output->writeln(sprintf('Created %d new currencies configured in domains.yaml.', $createdCurrenciesCount));

        return Command::SUCCESS;
    }
}
