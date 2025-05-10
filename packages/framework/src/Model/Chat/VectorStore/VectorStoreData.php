<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\VectorStore;

class VectorStoreData
{
    /**
     * @var string|null
     */
    public $uuid = null;

    /**
     * @var string|null
     */
    public $name = null;

    /**
     * @var string|null
     */
    public $description = null;

    /**
     * @var string|null
     */
    public $externalId = null;

    /**
     * @var string[]
     */
    public $dataStructure = [];
}
