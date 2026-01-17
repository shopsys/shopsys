<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Component\Console\Output\NullOutput;

abstract class AbstractExportCronModule implements SimpleCronModuleInterface
{
    public function __construct(
        protected readonly AbstractIndex $index,
        protected readonly IndexFacade $indexFacade,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger)
    {
    }

    #[Override]
    public function run()
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index::getName(), $domainId);
            $this->indexFacade->export($this->index, $indexDefinition, new NullOutput());
        }
    }
}
