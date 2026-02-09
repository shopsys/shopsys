<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportChangedCronModule;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex $index
 */
class BlogArticleExportChangedCronModule extends AbstractExportChangedCronModule
{
    public function __construct(
        BlogArticleIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
        ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain, $eventDispatcher);
    }
}
