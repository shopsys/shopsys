<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use OpenAI\Responses\Chat\CreateResponse;
use Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner;
use Shopsys\FrameworkBundle\Model\Chat\Chat;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessageFactory;

class OpenAiFunctionCallingFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiMapper $openAiMapper
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner $dynamicFunctionRunner
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessageFactory $chatMessageFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly OpenAiMapper $openAiMapper,
        protected readonly DynamicFunctionRunner $dynamicFunctionRunner,
        protected readonly ChatMessageFactory $chatMessageFactory,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \OpenAI\Responses\Chat\CreateResponse $response
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage|null
     */
    public function handleFunctionCalling(CreateResponse $response, ChatMessage $chatMessage): ?ChatMessage
    {
        $responseFunctionCallMessage = $response['choices'][0]['message'];

        if (isset($responseFunctionCallMessage['function_call']) === false) {
            return null;
        }

        $aiFunctionName = $responseFunctionCallMessage['function_call']['name'];
        $arguments = json_decode($responseFunctionCallMessage['function_call']['arguments'], true);
        $this->openAiMapper->mapOpenAiFunctionCallingRequestToChatMessage($response, $chatMessage);

        try {
            //call function by last response
            $result = $this->dynamicFunctionRunner->call($aiFunctionName, $arguments);
        } catch (LogicException $logicException) {
            //handle exception - fill answer with some message for user about failing OR TODO - handle error message with another agent(error handle agent) :-D
            //stop chat?
            $result = sprintf('LogicException: %s', $logicException->getMessage());
        }

        //create new ChatMessage for next AI request with result of function calling
        return $this->addFunctionCallResult($chatMessage->getChat(), $aiFunctionName, (string)$result);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $aiFunctionName
     * @param string $content
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    protected function addFunctionCallResult(Chat $chat, string $aiFunctionName, string $content): ChatMessage
    {
        $chatMessage = $this->chatMessageFactory->create($chat, ChatMessage::TYPE_FUNCTION);
        $chatMessage->setFunctionCallResult([
            'name' => $aiFunctionName,
            'content' => $content,
        ]);
        $chatMessage->setType(ChatMessage::TYPE_FUNCTION);

        $this->em->persist($chatMessage);
        $this->em->flush();

        $chat->addMessage($chatMessage);
        $this->em->flush();

        return $chatMessage;
    }
}
