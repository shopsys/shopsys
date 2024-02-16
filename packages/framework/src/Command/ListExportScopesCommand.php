<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeRegistry;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'shopsys:list:export-scopes')]
class ListExportScopesCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeRegistry $exportScopeRegistry
     */
    public function __construct(
        protected readonly ExportScopeRegistry $exportScopeRegistry
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scopesInformation = [];
        foreach ($this->exportScopeRegistry->getAllScopes() as $scope) {
            foreach ($scope->getElasticFieldNamesIndexedByEntityFieldNames() as $entityFieldName => $elasticFieldNames) {
                $scopesInformation[] = [
                    get_class($scope),
                    $entityFieldName,
                    implode(', ', array_map(
                        fn (ProductExportFieldEnum $exportFieldScopeEnum) => $exportFieldScopeEnum->value,
                        $elasticFieldNames,
                    )),
                    implode(', ', array_map(
                        fn (ProductExportPreconditionsEnum $preconditionEnum) => $preconditionEnum->name,
                        $scope->getPreconditions(),
                    )),
                ];
            }
        }
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->table(['Scope class name', 'Entity Field Name', 'Elasticsearch Field Names', 'Preconditions'], $scopesInformation);

        return Command::SUCCESS;
    }
}
