<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportChangedCronModule;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductExportChangedCronModule extends AbstractExportChangedCronModule
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        ProductIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
        ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain, $eventDispatcher);
    }
}
