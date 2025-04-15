<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Message;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Chat\Chat;

class ChatMessageFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    public function create(Chat $chat, string $question): ChatMessage
    {
        $entityClassName = $this->entityNameResolver->resolve(ChatMessage::class);

        return new $entityClassName($chat, $question);
    }
}
