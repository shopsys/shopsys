<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:entities:dump',
    description: 'Dump entities filepaths for use in coding standards',
)]
class EntitiesDumpCommand extends Command
{
    public const string OUTPUT_FILE = 'entities-dump.json';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected string $cacheDir,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entitiesFilepaths = [];

        foreach ($this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames() as $className) {
            $reflection = new ReflectionClass($className);
            $entitiesFilepaths[] = $reflection->getFileName();
        }

        $outputFilePath = $this->cacheDir . '/' . self::OUTPUT_FILE;

        file_put_contents(
            $outputFilePath,
            json_encode($entitiesFilepaths),
        );

        $output->writeln(sprintf(
            'Entities dumped into file: %s',
            $outputFilePath,
        ));

        return Command::SUCCESS;
    }
}
