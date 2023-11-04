<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Shopsys\MigrationBundle\Command\Exception\NoMigrationLocationException;
use Shopsys\MigrationBundle\Component\Doctrine\DatabaseSchemaFacade;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocation;
use Shopsys\MigrationBundle\Component\Generator\MigrationsGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'shopsys:migrations:generate')]
class GenerateMigrationCommand extends Command
{
    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;

    protected Configuration $configuration;

    /**
     * @param string $vendorDirectoryPath
     * @param \Shopsys\MigrationBundle\Component\Doctrine\DatabaseSchemaFacade $databaseSchemaFacade
     * @param \Shopsys\MigrationBundle\Component\Generator\MigrationsGenerator $migrationsGenerator
     * @param \Doctrine\Migrations\DependencyFactory $dependencyFactory
     */
    public function __construct(
        protected readonly string $vendorDirectoryPath,
        protected readonly DatabaseSchemaFacade $databaseSchemaFacade,
        protected readonly MigrationsGenerator $migrationsGenerator,
        DependencyFactory $dependencyFactory,
    ) {
        $this->configuration = $dependencyFactory->getConfiguration();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a new migration if need it');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking database schema...');

        $filteredSchemaDiffSqlCommands = $this->databaseSchemaFacade->getFilteredSchemaDiffSqlCommands();

        if (count($filteredSchemaDiffSqlCommands) === 0) {
            $output->writeln('<info>Database schema is satisfying ORM, no migrations were generated.</info>');

            return static::RETURN_CODE_OK;
        }

        $io = new SymfonyStyle($input, $output);

        try {
            $migrationsLocation = $this->chooseMigrationLocation($io);
        } catch (NoMigrationLocationException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return static::RETURN_CODE_ERROR;
        }

        $generatorResult = $this->migrationsGenerator->generate(
            $filteredSchemaDiffSqlCommands,
            $migrationsLocation,
        );

        if ($generatorResult->hasError()) {
            $output->writeln(
                '<error>Migration file "' . realpath($generatorResult->getMigrationFilePath()) . '" could not be saved.</error>',
            );

            return static::RETURN_CODE_ERROR;
        }

        $output->writeln('<info>Database schema is not satisfying ORM, a new migration was generated!</info>');
        $output->writeln(sprintf(
            '<info>Migration file "%s" was saved (%d B).</info>',
            realpath($generatorResult->getMigrationFilePath()),
            $generatorResult->getWrittenBytes(),
        ));

        return static::RETURN_CODE_OK;
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocation
     */
    protected function chooseMigrationLocation(SymfonyStyle $io): MigrationsLocation
    {
        $migrationDirectoriesIndexedByNamespace = $this->configuration->getMigrationDirectories();
        $availableNamespaces = [];

        foreach ($migrationDirectoriesIndexedByNamespace as $namespace => $migrationDirectory) {
            if (str_contains(realpath($migrationDirectory), realpath($this->vendorDirectoryPath)) === false) {
                $availableNamespaces[] = $namespace;
            }
        }

        if (count($availableNamespaces) === 0) {
            throw new NoMigrationLocationException();
        }

        if (count($availableNamespaces) > 1) {
            $chosenNamespace = $io->choice(
                'There is more than one namespace available as the destination of generated migrations. Which namespace would you like to choose?',
                $availableNamespaces,
            );

            return new MigrationsLocation($migrationDirectoriesIndexedByNamespace[$chosenNamespace], $chosenNamespace);
        }

        $firstNamespace = reset($availableNamespaces);

        return new MigrationsLocation($migrationDirectoriesIndexedByNamespace[$firstNamespace], $firstNamespace);
    }
}
