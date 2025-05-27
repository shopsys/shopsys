<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Application\Mapper;

use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage as EntityChatMessage;

class ChatResponseMapper
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse $chatResponse
     */
    public function mapResponseToChatMessage(ChatMessage $chatMessage, ChatResponse $chatResponse): void
    {
        $chatMessage->setAnswer($chatResponse->choices[0]->content);
        $this->mapTokensToEntity($chatMessage, $chatResponse->promptTokens, $chatResponse->completionTokens);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @param int $inputTokens
     * @param int $outputTokens
     */
    protected function mapTokensToEntity(
        EntityChatMessage $chatMessage,
        int $inputTokens,
        int $outputTokens,
    ): void {
        $chatMessage->setInputTokens($inputTokens);
        $chatMessage->setOutputTokens($outputTokens);
    }
}
