<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractElasticsearchIndexCommand extends Command
{
    private const ARGUMENT_INDEX_NAME = 'name';
    protected const OPTION_DOMAIN_ID = 'domainId';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry $indexRegistry
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly IndexRegistry $indexRegistry,
        protected readonly IndexFacade $indexFacade,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_INDEX_NAME,
                InputArgument::OPTIONAL,
                $this->getArgumentNameDescription(),
            )
            ->addOption(
                static::OPTION_DOMAIN_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Limit command to only one domain.',
            )
            ->setDescription($this->getCommandDescription());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);
        $indexName = $input->getArgument(self::ARGUMENT_INDEX_NAME);
        $domainId = $input->getOption(static::OPTION_DOMAIN_ID) !== null ? (int)$input->getOption(static::OPTION_DOMAIN_ID) : null;
        $output->writeln($this->getActionStartedMessage());

        foreach ($this->getAffectedIndexes($indexName) as $index) {
            $this->executeForIndex($output, $index, $domainId);
        }

        $symfonyStyleIo->success($this->getActionFinishedMessage());

        return Command::SUCCESS;
    }

    /**
     * @param string|null $indexName
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex[]
     */
    private function getAffectedIndexes(?string $indexName): array
    {
        if ($indexName) {
            return [$this->indexRegistry->getIndexByIndexName($indexName)];
        }

        return $this->indexRegistry->getRegisteredIndexes();
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param int|null $domainId
     */
    protected function executeForIndex(
        OutputInterface $output,
        AbstractIndex $index,
        ?int $domainId = null,
    ): void {
        if ($domainId !== null) {
            $domainConfig = $this->domain->getDomainConfigById($domainId);

            $this->executeCommand(
                $this->indexDefinitionLoader->getIndexDefinition($index::getName(), $domainConfig->getId()),
                $output,
            );
        } else {
            foreach ($this->domain->getAll() as $domainConfig) {
                $this->executeCommand(
                    $this->indexDefinitionLoader->getIndexDefinition($index::getName(), $domainConfig->getId()),
                    $output,
                );
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    abstract protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void;

    /**
     * @return string
     */
    abstract protected function getCommandDescription(): string;

    /**
     * @return string
     */
    abstract protected function getArgumentNameDescription(): string;

    /**
     * @return string
     */
    abstract protected function getActionStartedMessage(): string;

    /**
     * @return string
     */
    abstract protected function getActionFinishedMessage(): string;
}
