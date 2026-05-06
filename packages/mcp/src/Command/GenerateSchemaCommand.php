<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Command;

use Override;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;
use Shopsys\McpBundle\Component\Database\Schema\McpSchemaFileGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Generates the MCP database schema artifact JSON file.',
)]
class GenerateSchemaCommand extends Command
{
    public const string COMMAND_NAME = 'shopsys:mcp:generate-schema';

    public function __construct(
        protected readonly McpSchemaFileGenerator $mcpSchemaFileGenerator,
        protected readonly ExposedSchemaProvider $exposedSchemaProvider,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schemaFilePath = $this->exposedSchemaProvider->getSchemaFilePath();

        if (!$this->mcpSchemaFileGenerator->generateSchemaFile()) {
            $io->success(sprintf('MCP schema is up to date: %s', $schemaFilePath));

            return Command::SUCCESS;
        }

        $io->success(sprintf('MCP schema was generated in %s.', $schemaFilePath));

        return Command::SUCCESS;
    }
}
