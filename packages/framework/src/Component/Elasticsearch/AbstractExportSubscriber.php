<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractExportSubscriber implements EventSubscriberInterface
{
    protected AbstractExportScheduler $exportScheduler;

    protected EntityManagerInterface $entityManager;

    protected IndexFacade $indexFacade;

    protected IndexDefinitionLoader $indexDefinitionLoader;

    protected AbstractIndex $index;

    protected Domain $domain;

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
     * {@inheritdoc}
     */
    abstract public static function getSubscribedEvents(): array;

    public function exportScheduledRows(): void
    {
        if ($this->exportScheduler->hasAnyRowIdsForImmediateExport()) {
            // to be sure the recalculated data are fetched from database properly
            $this->entityManager->clear();

            $productIds = $this->exportScheduler->getRowIdsForImmediateExport();

            foreach ($this->domain->getAllIds() as $domainId) {
                $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(
                    $this->index::getName(),
                    $domainId
                );
                $this->indexFacade->exportIds($this->index, $indexDefinition, $productIds);
            }
        }
    }
}
