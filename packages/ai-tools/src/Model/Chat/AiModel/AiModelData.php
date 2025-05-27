<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\AiModel;

class AiModelData
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var bool|null
     */
    public $isActive;

    /**
     * @var bool|null
     */
    public $isDeprecated;

    /**
     * @var \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum
     */
    public $apiSource;

    public function __construct()
    {
        $this->name = null;
        $this->description = null;
        $this->isActive = true;
        $this->isDeprecated = false;
        $this->apiSource = AiModelApiSourceEnum::OPENAI;
    }
}
