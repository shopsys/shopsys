<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent;

use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel;

class AgentData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var bool|null
     */
    public $enabled;

    /**
     * @var AiModel|null
     */
    public $aiModel;

    /**
     * @var string|null
     */
    public $setup = '';

    /**
     * @var string|null
     */
    public $internalKey;

    /**
     * @var string[]
     */
    public $availableAiFunctions = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore[]
     */
    public $vectorStores = [];
}
