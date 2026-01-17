<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportCronModule;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;

class BlogArticleExportCronModule extends AbstractExportCronModule
{
    public function __construct(
        BlogArticleIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain);
    }
}
