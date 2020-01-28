<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

abstract class AbstractIndex
{
    protected const BATCH_SIZE = 100;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\DataProviderInterface $dataProvider
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Elasticsearch\DataProviderInterface
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
