<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

class ChatDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\ChatData
     */
    protected function createInstance(): ChatData
    {
        return new ChatData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\ChatData
     */
    public function create(): ChatData
    {
        return $this->createInstance();
    }
}
