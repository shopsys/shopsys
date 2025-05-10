<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Shopsys\FrameworkBundle\Model\Chat\Chat;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore;

class OpenAiRequestFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiFunctionCallingFactory $openAiFunctionCallingFactory
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiToolsFactory $openAiToolsFactory
     */
    public function __construct(
        protected readonly OpenAiFunctionCallingFactory $openAiFunctionCallingFactory,
        protected readonly OpenAiToolsFactory $openAiToolsFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return array
     */
    public function getCreateVectorStoreRequest(VectorStore $vectorStore): array
    {
        return [
            'name' => $vectorStore->getName(),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @return array
     */
    public function getOpenAiChatRequest(Chat $chat): array
    {
        $request = [];
        $request['model'] = $chat->getAgent()->getModel();
        $request['messages'] = $this->getMessages($chat);

        $functions = $this->openAiFunctionCallingFactory->getFunctions($chat->getAgent());

        if (count($functions) > 0) {
            $request['functions'] = $functions;
            $request['function_call'] = 'auto';
        }

        //        $tools = $this->openAiToolsFactory->getTools($chat->getAgent());

        //        if ($tools !== null) {
        //            $request['tools'] = $tools;
        //
        //            $availableVectorStores = array_filter($chat->getAgent()->getVectorStores(), fn (VectorStore $vectorStore) => $vectorStore->getExternalId() !== null);
        //            $request['vector_store_ids'] = array_map(fn (VectorStore $vectorStore) => $vectorStore->getExternalId(), $availableVectorStores);
        //        }
        //d($request);
        return $request;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @return array
     */
    protected function getMessages(Chat $chat): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $chat->getAgent()->getSetup(),
            ],
        ];

        foreach ($chat->getMessages() as $message) {
            //question part
            match ($message->getType()) {
                ChatMessage::TYPE_FUNCTION => $this->extractFunctionQuestion($messages, $message),
                default => $this->extractMessageQuestion($messages, $message),
            };

            //answerPart
            $this->extractAnswer($messages, $message);
        }

        return $messages;
    }

    /**
     * @param array $messages
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $message
     */
    protected function extractAnswer(array &$messages, ChatMessage $message): void
    {
        if ($message->getAnswer() === null) {
            return;
        }

        match ($message->getAnswer()) {
            ChatMessage::TYPE_FUNCTION => $this->extractFunctionAnswer($messages, $message),
            default => $this->extractMessageAnswer($messages, $message),
        };
    }

    /**
     * @param array $messages
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $message
     */
    protected function extractFunctionAnswer(array &$messages, ChatMessage $message): void
    {
        $messages[] = [
            'role' => 'assistant',
            'function_call' => $message->getFunctionCall(),
        ];
    }

    /**
     * @param array $messages
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $message
     */
    protected function extractMessageAnswer(array &$messages, ChatMessage $message): void
    {
        $messages[] = [
            'role' => 'assistant',
            'content' => $message->getAnswer(),
        ];
    }

    /**
     * @param array $messages
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $message
     */
    protected function extractMessageQuestion(array &$messages, ChatMessage $message): void
    {
        $messages[] = [
            'role' => 'user',
            'content' => $message->getQuestion(),
        ];
    }

    /**
     * @param array $messages
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $message
     */
    protected function extractFunctionQuestion(array &$messages, ChatMessage $message): void
    {
        $functionCallResult = $message->getFunctionCallResult();

        $messages[] = [
            'role' => 'function',
            'name' => $functionCallResult['name'],
            'content' => (string)$functionCallResult['content'],
        ];
    }
}
