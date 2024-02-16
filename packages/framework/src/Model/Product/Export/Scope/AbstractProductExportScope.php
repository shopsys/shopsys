<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface;

abstract class AbstractProductExportScope implements ExportScopeInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum[]
     */
    public function getPreconditions(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getEntityFieldNames(): array
    {
        return array_keys($this->getElasticFieldNamesIndexedByEntityFieldNames());
    }
}
