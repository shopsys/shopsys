<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportSubscriber;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Symfony\Component\HttpKernel\KernelEvents;

class BlogArticleExportSubscriber extends AbstractExportSubscriber
{
    public function __construct(
        BlogArticleExportScheduler $blogArticleExportScheduler,
        EntityManagerInterface $entityManager,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        BlogArticleIndex $index,
        Domain $domain,
    ) {
        parent::__construct(
            $blogArticleExportScheduler,
            $entityManager,
            $indexFacade,
            $indexDefinitionLoader,
            $index,
            $domain,
        );
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['exportScheduledRows', -30],
            ],
        ];
    }
}
