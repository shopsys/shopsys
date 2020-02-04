<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

abstract class AbstractIndex
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\DataProviderInterface $dataProvider
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\DataProviderInterface
     */
    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;
}
