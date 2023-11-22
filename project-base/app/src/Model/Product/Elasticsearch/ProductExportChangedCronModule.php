<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportChangedCronModule as BaseProductExportChangedCronModule;

class ProductExportChangedCronModule extends BaseProductExportChangedCronModule
{
    public function run(): void
    {
        parent::run();

        $this->eventDispatcher->dispatch(
            new IndexExportedEvent($this->index),
            IndexExportedEvent::INDEX_EXPORTED,
        );
    }
}
