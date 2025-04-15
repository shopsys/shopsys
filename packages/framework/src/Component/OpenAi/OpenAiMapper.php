<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use OpenAI\Responses\Chat\CreateResponse;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;

class OpenAiMapper
{
    /**
     * @param \OpenAI\Responses\Chat\CreateResponse $response
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $chatMessage
     */
    public function mapOpenAiChatResponseToChatMessage(CreateResponse $response, ChatMessage $chatMessage): void
    {
        $answer = $response['choices'][0]['message']['content'];

        $usage = $response->usage;
        $chatMessage->setAnswer($answer);
        $chatMessage->setInputTokens($usage->promptTokens);
        $chatMessage->setOutputTokens($usage->completionTokens);
        $chatMessage->setTotalTokens($usage->totalTokens);
    }
}
