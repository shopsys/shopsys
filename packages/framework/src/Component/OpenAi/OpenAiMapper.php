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
        $chatMessage->setAnswer($answer);

        $this->mapUsageResponseToChatMessage($response, $chatMessage);
    }

    /**
     * @param \OpenAI\Responses\Chat\CreateResponse $response
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $chatMessage
     */
    protected function mapUsageResponseToChatMessage(CreateResponse $response, ChatMessage $chatMessage): void
    {
        $usage = $response->usage;
        $chatMessage->setInputTokens($usage->promptTokens);
        $chatMessage->setOutputTokens($usage->completionTokens);
        $chatMessage->setTotalTokens($usage->totalTokens);
    }

    /**
     * @param \OpenAI\Responses\Chat\CreateResponse $response
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $chatMessage
     */
    public function mapOpenAiFunctionCallingRequestToChatMessage(
        CreateResponse $response,
        ChatMessage $chatMessage,
    ): void {
        $responseFunctionCallMessage = $response['choices'][0]['message'];
        $chatMessage->setFunctionCall([
            'name' => $responseFunctionCallMessage['function_call']['name'],
            'arguments' => json_decode($responseFunctionCallMessage['function_call']['arguments']),
        ]);

        $this->mapUsageResponseToChatMessage($response, $chatMessage);
    }
}
