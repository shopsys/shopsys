<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportChangedCronModule as BaseProductExportChangedCronModule;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductExportChangedCronModule extends BaseProductExportChangedCronModule
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \App\Component\Domain\Domain $domain
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ProductIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run()
    {
        parent::run();
        $this->eventDispatcher->dispatch(
            new IndexExportedEvent($this->index),
            IndexExportedEvent::INDEX_EXPORTED
        );
    }
}
