<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeRegistry;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\Scope\ProductBrandExportScope;
use Shopsys\FrameworkBundle\Model\Product\Export\Scope\ProductPriceExportScope;
use Shopsys\FrameworkBundle\Model\Product\Export\Scope\ProductVisibilityExportScope;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
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
        protected readonly Domain $domain,
        protected readonly ProductFacade $productFactory,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dummyObject = $this->createDummyObject();
        $domain = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        $scopesInformation = [];
        foreach ($this->exportScopeRegistry->getAllScopes() as $scope) {
            $scopesInformation[] = [
                get_class($scope),
                implode(', ', array_keys($scope->map($dummyObject, $domain->getLocale(), $domain->getId()))),
                implode(', ', array_map(
                    fn (string $dependency) => $dependency,
                    $scope->getDependencies(),
                )),
                implode(', ', array_map(
                    fn (ProductExportPreconditionsEnum $preconditionEnum) => $preconditionEnum->name,
                    $scope->getPreconditions(),
                )),
            ];
        }
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->table(['Scope class name', 'Elasticsearch Field Names', 'Dependencies', 'Preconditions'], $scopesInformation);

        return Command::SUCCESS;
    }

    // TODO: nějak upravit, ať nemusíme vracet produkt
    private function createDummyObject(): object
    {
        return $this->productFactory->getById(1);
    }
}
