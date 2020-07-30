<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractExportChangedCronModule implements SimpleCronModuleInterface
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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        AbstractIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->index = $index;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * @required
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        if ($this->eventDispatcher !== null && $this->eventDispatcher !== $eventDispatcher) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->eventDispatcher === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->eventDispatcher = $eventDispatcher;
        }
    }

    public function run()
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition($this->index::getName(), $domainId);
            $this->indexFacade->exportChanged($this->index, $indexDefinition, new NullOutput());
        }

        $this->eventDispatcher->dispatch(new IndexExportedEvent($this->index), IndexExportedEvent::INDEX_EXPORTED);
    }
}
