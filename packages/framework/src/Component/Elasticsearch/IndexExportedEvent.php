<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Symfony\Contracts\EventDispatcher\Event;

class IndexExportedEvent extends Event
{
    public const INDEX_EXPORTED = 'elasticsearch.index.exported';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     */
    public function __construct(protected readonly AbstractIndex $index)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    public function getIndex(): AbstractIndex
    {
        return $this->index;
    }
}
