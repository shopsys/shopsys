<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command;

use Shopsys\MigrationBundle\Component\Doctrine\DatabaseSchemaFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDatabaseSchemaCommand extends Command
{
    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:migrations:check-schema';

    /**
     * @param \Shopsys\MigrationBundle\Component\Doctrine\DatabaseSchemaFacade $databaseSchemaFacade
     */
    public function __construct(protected readonly DatabaseSchemaFacade $databaseSchemaFacade)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Check if database schema is satisfying ORM');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking database schema...');

        $filteredSchemaDiffSqlCommands = $this->databaseSchemaFacade->getFilteredSchemaDiffSqlCommands();

        if (count($filteredSchemaDiffSqlCommands) === 0) {
            $output->writeln('<info>Database schema is satisfying ORM.</info>');

            return static::RETURN_CODE_OK;
        }

        $output->writeln('<error>Database schema is not satisfying ORM!</error>');
        $output->writeln('<error>Following SQL commands should fix the problem (revise them before!):</error>');
        $output->writeln('');

        foreach ($filteredSchemaDiffSqlCommands as $sqlCommand) {
            $output->writeln('<fg=red>' . $sqlCommand . ';</fg=red>');
        }
        $output->writeln('<info>TIP: you can use shopsys:migrations:generate</info>');
        $output->writeln('');

        return static::RETURN_CODE_ERROR;
    }
}
