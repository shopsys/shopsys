<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Output\NullOutput;

abstract class AbstractExportCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    protected $index;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade
     */
    protected $indexFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        AbstractIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain
    ) {
        $this->index = $index;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index, $domainId);
            $this->indexFacade->exportByIndexDefinition($indexDefinition, new NullOutput());
        }
    }
}
