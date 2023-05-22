<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EntitiesDumpCommand extends Command
{
    private const OUTPUT_FILE = 'entities-dump.json';

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:entities:dump';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param string $cacheDir
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected string $cacheDir,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Dump entities filepaths for use in coding standards');
    }

    /**
     * {@inheritdoc}
     */
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
