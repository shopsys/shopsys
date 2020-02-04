<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

abstract class AbstractExportListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportScheduler
     */
    protected $exportScheduler;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository
     */
    protected $indexRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex
     */
    protected $index;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportScheduler $exportScheduler
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository $indexRepository
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        AbstractExportScheduler $exportScheduler,
        EntityManagerInterface $entityManager,
        IndexRepository $indexRepository,
        IndexDefinitionLoader $indexDefinitionLoader,
        ProductIndex $index,
        Domain $domain
    ) {
        $this->exportScheduler = $exportScheduler;
        $this->entityManager = $entityManager;
        $this->indexRepository = $indexRepository;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->index = $index;
        $this->domain = $domain;
    }

    public function exportScheduledRows(): void
    {
        if ($this->exportScheduler->hasAnyRowIdsForImmediateExport()) {
            // to be sure the recalculated data are fetched from database properly
            $this->entityManager->clear();

            $productIds = $this->exportScheduler->getRowIdsForImmediateExport();

            foreach ($this->domain->getAllIds() as $domainId) {
                $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index, $domainId);
                $this->indexRepository->export($indexDefinition, $productIds, new NullOutput());
            }
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $filterResponseEvent
     */
    public function onKernelResponse(FilterResponseEvent $filterResponseEvent): void
    {
        $this->exportScheduledRows();
    }
}
