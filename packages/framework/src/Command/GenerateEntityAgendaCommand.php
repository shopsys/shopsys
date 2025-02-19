<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Exception;
use RuntimeException;
use Shopsys\FrameworkBundle\Maker\BaseMaker;
use Shopsys\FrameworkBundle\Maker\DataFixtureMaker;
use Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfigFactory;
use Shopsys\FrameworkBundle\Maker\EntityMaker;
use Shopsys\FrameworkBundle\Maker\FacadeMaker;
use Shopsys\FrameworkBundle\Maker\NotFoundExceptionMaker;
use Shopsys\FrameworkBundle\Maker\RepositoryMaker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'make:shopsys:entity-agenda',
    description: 'Generates entity, data object, data object factory, facade, repository, and data fixture classes for the given entity name',
)]
class GenerateEntityAgendaCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfigFactory $entityConfigFactory
     */
    public function __construct(protected readonly EntityConfigFactory $entityConfigFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument(BaseMaker::ENTITY_NAME_ARGUMENT, InputArgument::REQUIRED, 'The entity name (e.g. <fg=yellow>Kitty</>)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityConfig = $this->entityConfigFactory->create($input, $io);

        $application = $this->getApplication();

        if ($application === null) {
            throw new RuntimeException('Application instance is not available');
        }

        $commandNames = [
            EntityMaker::getCommandName(),
            RepositoryMaker::getCommandName(),
            FacadeMaker::getCommandName(),
            NotFoundExceptionMaker::getCommandName(),
            DataFixtureMaker::getCommandName(),
        ];

        foreach ($commandNames as $commandName) {
            $commandInputParameters = [
                'command' => $commandName,
                BaseMaker::ENTITY_NAME_ARGUMENT => $entityConfig->entityName,
            ];

            if ($commandName === EntityMaker::getCommandName()) {
                $commandInputParameters += [
                    '--' . EntityMaker::TABLE_NAME_OPTION => $entityConfig->tableName,
                    '--' . EntityMaker::IS_TRANSLATABLE_OPTION => $entityConfig->isTranslatable,
                    '--' . EntityMaker::HAS_ID_OPTION => $entityConfig->hasId,
                    '--' . EntityMaker::HAS_UUID_OPTION => $entityConfig->hasUuid,
                ];
            }

            $commandInput = new ArrayInput($commandInputParameters);

            try {
                $application->doRun($commandInput, $output);
            } catch (Exception $e) {
                $io->error(sprintf('Error running command %s: %s', $commandName, $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
