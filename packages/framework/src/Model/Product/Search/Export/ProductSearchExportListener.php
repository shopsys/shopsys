<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportListener;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;

class ProductSearchExportListener extends AbstractExportListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportScheduler $productSearchExportScheduler
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository $indexRepository
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductSearchExportScheduler $productSearchExportScheduler,
        EntityManagerInterface $entityManager,
        IndexRepository $indexRepository,
        IndexDefinitionLoader $indexDefinitionLoader,
        ProductIndex $index,
        Domain $domain
    ) {
        parent::__construct($productSearchExportScheduler, $entityManager, $indexRepository, $indexDefinitionLoader, $index, $domain);
    }
}
