<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Shopsys\FrameworkBundle\Event\DatabaseSchemaPreparedEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'shopsys:schema:create',
    description: 'Create database public schema',
)]
class CreateDatabaseSchemaCommand extends Command
{
    public function __construct(
        private readonly DatabaseSchemaFacade $databaseSchemaFacade,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Initializing database schema');
        $this->databaseSchemaFacade->createSchema('public');
        $databaseSchemaPreparedEvent = new DatabaseSchemaPreparedEvent();
        $this->eventDispatcher->dispatch($databaseSchemaPreparedEvent);

        foreach ($databaseSchemaPreparedEvent->getMessages() as $message) {
            $output->writeln($message);
        }

        $output->writeln('Database schema created successfully!');

        return Command::SUCCESS;
    }
}
