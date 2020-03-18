<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Symfony\Contracts\EventDispatcher\Event;

class IndexExportedEvent extends Event
{
    public const INDEX_EXPORTED = 'elasticsearch.index.exported';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    protected $index;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     */
    public function __construct(AbstractIndex $index)
    {
        $this->index = $index;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    public function getIndex(): AbstractIndex
    {
        return $this->index;
    }
}
