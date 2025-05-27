<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat;

class ChatData
{
    /**
     * @var \Shopsys\AiToolsBundle\Model\Chat\Agent\Agent|null
     */
    public $agent;

    /**
     * @var string|null
     */
    public $identifier;
}
