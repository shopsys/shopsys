<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent;

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
     * @var string|null
     */
    public $model;

    /**
     * @var string|null
     */
    public $setup = '';

    /**
     * @var string|null
     */
    public $internalKey;
}
