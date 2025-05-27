<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Agent;

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
     * @var \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel|null
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
     * @var \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore[]
     */
    public $vectorStores = [];
}
