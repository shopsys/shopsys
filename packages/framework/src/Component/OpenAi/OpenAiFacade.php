<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;

class OpenAiFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiClient $openAiClient
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiMapper $openAiMapper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OpenAiClient $openAiClient,
        protected readonly OpenAiMapper $openAiMapper,

        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    public function handleQuestion(ChatMessage $chatMessage): ChatMessage
    {
        try {
            $response = $this->openAiClient->askSimpleQuestion($chatMessage->getChat());
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            $chatMessage->setAnswer(t('Sorry, something went wrong.'));
            $this->em->flush();

            return $chatMessage;
        }


        // validation? is possible validate response?

        $this->openAiMapper->mapOpenAiChatResponseToChatMessage($response, $chatMessage);

        $this->em->flush();

        return $chatMessage;
    }
}
