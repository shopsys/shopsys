<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Application;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Shopsys\AiToolsBundle\Component\Ai\Application\Mapper\ChatRequestMapper;
use Shopsys\AiToolsBundle\Component\Ai\Application\Mapper\ChatResponseMapper;
use Shopsys\AiToolsBundle\Component\Ai\Client\AIClientFactory;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse;
use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner;
use Shopsys\AiToolsBundle\Model\Chat\Chat;
use Shopsys\AiToolsBundle\Model\Chat\ChatFacade;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage as ChatMessageEntity;
use Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessageFactory;

class AiChatSessionFacade
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Client\AIClientFactory $clientFactory
     * @param \Shopsys\AiToolsBundle\Model\Chat\ChatFacade $chatFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner $dynamicFunctionRunner
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessageFactory $chatMessageFactory
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\Mapper\ChatRequestMapper $chatRequestMapper
     * @param \Shopsys\AiToolsBundle\Component\Ai\Application\Mapper\ChatResponseMapper $chatResponseMapper
     */
    public function __construct(
        protected readonly AIClientFactory $clientFactory,
        protected readonly ChatFacade $chatFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly DynamicFunctionRunner $dynamicFunctionRunner,
        protected readonly ChatMessageFactory $chatMessageFactory,
        protected readonly ChatRequestMapper $chatRequestMapper,
        protected readonly ChatResponseMapper $chatResponseMapper,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Chat $chat
     * @param string $userMessage
     * @return \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage
     */
    public function ask(Chat $chat, string $userMessage): ChatMessageEntity
    {
        $chatMessage = $this->chatFacade->addQuestion($chat, $userMessage);

        return $this->handleMessage($chatMessage);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @return \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage
     */
    protected function handleMessage(ChatMessageEntity $chatMessage): ChatMessageEntity
    {
        $agent = $chatMessage->getChat()->getAgent();

        $client = $this->clientFactory->getClientByAgent($agent);
        $chatResponse = $client->chat($this->chatRequestMapper->createChatRequest($chatMessage));

        $functionCallingChatMessage = $this->handleFunctionCalling($chatResponse, $chatMessage);

        if ($functionCallingChatMessage) {
            return $this->handleMessage($functionCallingChatMessage);
        }

        $this->chatResponseMapper->mapResponseToChatMessage($chatMessage, $chatResponse);

        $this->em->flush();

        return $chatMessage;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse $response
     * @param \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @return \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage|null
     */
    protected function handleFunctionCalling(
        ChatResponse $response,
        ChatMessageEntity $chatMessage,
    ): ?ChatMessageEntity {
        $message = $response->choices[0];

        if ($message->functionCall === null) {
            return null;
        }

        $chatMessage->setFunctionCall([
            'name' => $message->functionCall->name,
            'arguments' => $message->functionCall->arguments,
        ]);

        try {
            //call function by last response
            $result = $this->dynamicFunctionRunner->call(
                $message->functionCall->name,
                $message->functionCall->arguments,
            );
        } catch (LogicException $logicException) {
            $result = sprintf('LogicException: %s', $logicException->getMessage());
        }

        //create new ChatMessage for next AI request with result of function calling
        return $this->addFunctionCallResult($chatMessage->getChat(), $message->functionCall->name, (string)$result);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Chat $chat
     * @param string $aiFunctionName
     * @param string $content
     * @return \Shopsys\AiToolsBundle\Model\Chat\Message\ChatMessage
     */
    protected function addFunctionCallResult(
        Chat $chat,
        string $aiFunctionName,
        string $content,
    ): ChatMessageEntity {
        $chatMessage = $this->chatMessageFactory->create($chat, ChatMessageEntity::TYPE_FUNCTION);
        $chatMessage->setFunctionCallResult([
            'name' => $aiFunctionName,
            'content' => $content,
        ]);
        $chatMessage->setType(ChatMessageEntity::TYPE_FUNCTION);

        $this->em->persist($chatMessage);
        $this->em->flush();

        $chat->addMessage($chatMessage);
        $this->em->flush();

        return $chatMessage;
    }
}
