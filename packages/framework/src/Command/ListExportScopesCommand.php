<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:list:export-scopes',
    description: 'List all export scopes with their fields and preconditions',
)]
class ListExportScopesCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig $productExportScopeConfig
     */
    public function __construct(
        protected readonly ProductExportScopeConfig $productExportScopeConfig,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];
        $productExportScopeRules = $this->productExportScopeConfig->getProductExportScopeRules();
        $rulesCount = count($productExportScopeRules);
        $counter = 0;

        foreach ($productExportScopeRules as $productExportScope => $rule) {
            $rows[] = [
                $productExportScope,
                implode(', ', $rule->productExportFields),
                implode(', ', $rule->productExportPreconditions),
            ];

            if ($counter < $rulesCount - 1) {
                $rows[] = new TableSeparator();
            }
            $counter++;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Export Scope Name', 'Elasticsearch Field Names', 'Preconditions'])
            ->setRows($rows)
            ->setColumnMaxWidth(0, 30)
            ->setColumnMaxWidth(1, 80)
            ->setColumnMaxWidth(2, 50)
            ->setStyle('box-double')
            ->render();

        return Command::SUCCESS;
    }
}
