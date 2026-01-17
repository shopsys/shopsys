<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractExportSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly AbstractExportScheduler $exportScheduler,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly IndexFacade $indexFacade,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly AbstractIndex $index,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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
                    $domainId,
                );
                $this->indexFacade->exportIds($this->index, $indexDefinition, $productIds);
            }
        }
    }
}
