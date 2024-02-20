<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputCasesProvider;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeRegistry;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\Scope\ProductExportScopeInputEnum;
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
        protected readonly ExportScopeRegistry $exportScopeRegistry,
        protected readonly ExportScopeInputCasesProvider $exportScopeInputCasesProvider,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scopesInformation = [];
        foreach ($this->exportScopeRegistry->getAllScopes() as $exportScope) {
        }
//        // ProductExportScopeInputEnum::cases(); -  // TODO tohle je problém při rozšiřování - možná to neutně nemusí být enum?? tady si můžu posbírat všechny možné (nějaký EnumCasesProvider)
//        foreach ($this->exportScopeInputCasesProvider->getCases() as $exportScopeInput) {
//            foreach ($this->exportScopeRegistry->getScopesByPropertyNames([$exportScopeInput]) as $scope) {
//                $scopesInformation[] = [
//                    get_class($scope),
//                    $exportScopeInput->value,
//                    implode(', ', array_map(
//                        fn(ProductExportFieldEnum $exportFieldScopeEnum) => $exportFieldScopeEnum->value,
//                        $scope->getElasticFieldsByScopeInput($exportScopeInput),
//                    )),
//                    implode(', ', array_map(
//                        fn(ProductExportPreconditionsEnum $preconditionEnum) => $preconditionEnum->name,
//                        $scope->getPreconditions(),
//                    )),
//                ];
//            }
//        }
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->table(['Scope class name', 'Input', 'Elasticsearch Field Names', 'Preconditions'], $scopesInformation);

        return Command::SUCCESS;
    }
}
