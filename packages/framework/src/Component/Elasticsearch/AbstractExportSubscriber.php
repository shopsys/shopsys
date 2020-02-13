<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractExportSubscriber implements EventSubscriberInterface
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
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade
     */
    protected $indexFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    protected $index;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportScheduler $exportScheduler
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        AbstractExportScheduler $exportScheduler,
        EntityManagerInterface $entityManager,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        AbstractIndex $index,
        Domain $domain
    ) {
        $this->exportScheduler = $exportScheduler;
        $this->entityManager = $entityManager;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->index = $index;
        $this->domain = $domain;
    }

    /**
     * @inheritDoc
     */
    abstract public static function getSubscribedEvents(): array;

    public function exportScheduledRows(): void
    {
        if ($this->exportScheduler->hasAnyRowIdsForImmediateExport()) {
            // to be sure the recalculated data are fetched from database properly
            $this->entityManager->clear();

            $productIds = $this->exportScheduler->getRowIdsForImmediateExport();

            foreach ($this->domain->getAllIds() as $domainId) {
                $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index::getName(), $domainId);
                $this->indexFacade->exportIds($this->index, $indexDefinition, $productIds);
            }
        }
    }
}
