<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportSubscriber;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Symfony\Component\HttpKernel\KernelEvents;

class ArticleExportSubscriber extends AbstractExportSubscriber
{
    /**
     * @param \App\Model\Article\Elasticsearch\ArticleExportScheduler $articleExportScheduler
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \App\Model\Article\Elasticsearch\ArticleIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ArticleExportScheduler $articleExportScheduler,
        EntityManagerInterface $entityManager,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        ArticleIndex $index,
        Domain $domain,
    ) {
        parent::__construct(
            $articleExportScheduler,
            $entityManager,
            $indexFacade,
            $indexDefinitionLoader,
            $index,
            $domain,
        );
    }

    /**
     * @return array<'kernel.response', array<'exportScheduledRows'[]|int[]>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['exportScheduledRows', -30],
            ],
        ];
    }
}
