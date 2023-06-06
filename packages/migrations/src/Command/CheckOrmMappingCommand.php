<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckOrmMappingCommand extends Command
{
    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $defaultName = 'shopsys:migrations:check-mapping';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Check if ORM mapping is valid');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking ORM mapping...');

        $schemaValidator = new SchemaValidator($this->em);
        $schemaErrors = $schemaValidator->validateMapping();

        if (count($schemaErrors) > 0) {
            foreach ($schemaErrors as $className => $classErrors) {
                $output->writeln('<error>The entity-class ' . $className . ' mapping is invalid:</error>');

                foreach ($classErrors as $classError) {
                    $output->writeln('<error>- ' . $classError . '</error>');
                }

                $output->writeln('');
            }

            return static::RETURN_CODE_ERROR;
        }

        $output->writeln('<info>ORM mapping is valid.</info>');

        return static::RETURN_CODE_OK;
    }
}
