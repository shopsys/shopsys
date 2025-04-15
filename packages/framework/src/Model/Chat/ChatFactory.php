<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ChatFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatData $chatData
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat
     */
    public function create(ChatData $chatData): Chat
    {
        $entityClassName = $this->entityNameResolver->resolve(Chat::class);

        return new $entityClassName($chatData);
    }
}
