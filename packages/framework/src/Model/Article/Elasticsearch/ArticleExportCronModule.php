<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportCronModule;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;

class ArticleExportCronModule extends AbstractExportCronModule
{
    public function __construct(
        ArticleIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain);
    }
}
