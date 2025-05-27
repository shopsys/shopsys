<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Message;

use Shopsys\AiToolsBundle\Model\Chat\Chat;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

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
     * @param \Shopsys\AiToolsBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage
     */
    public function create(Chat $chat, string $question): ChatMessage
    {
        $entityClassName = $this->entityNameResolver->resolve(ChatMessage::class);

        return new $entityClassName($chat, $question);
    }
}
