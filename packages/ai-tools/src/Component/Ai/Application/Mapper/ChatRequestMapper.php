<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Application\Mapper;

use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRequest;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage as EntityChatMessage;

class ChatRequestMapper
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\Mapper\ChatMessageMapper $chatMessageMapper
     */
    public function __construct(
        protected readonly ChatMessageMapper $chatMessageMapper,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $entity
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRequest
     */
    public function createChatRequest(EntityChatMessage $entity): ChatRequest
    {
        $chat = $entity->getChat();
        $agent = $chat->getAgent();

        return new ChatRequest(
            messages: $this->chatMessageMapper->mapChatToDtoChatMessages($entity),
            model: $agent->getAiModel()->getName(),
            functions: $agent->getAvailableAiFunctions(),
        );
    }
}
