<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat;

class ChatDataFactory
{
    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\ChatData
     */
    protected function createInstance(): ChatData
    {
        return new ChatData();
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\ChatData
     */
    public function create(): ChatData
    {
        return $this->createInstance();
    }
}
