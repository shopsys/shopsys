<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore;

class OpenAiFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiClient $openAiClient
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiMapper $openAiMapper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiFunctionCallingFacade $openAiFunctionCallingFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OpenAiClient $openAiClient,
        protected readonly OpenAiMapper $openAiMapper,
        protected readonly LoggerInterface $logger,
        protected readonly OpenAiFunctionCallingFacade $openAiFunctionCallingFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage $chatMessage
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    public function handleQuestion(ChatMessage $chatMessage): ChatMessage
    {
        try {
            $response = $this->openAiClient->askChatQuestion($chatMessage->getChat());
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            $chatMessage->setAnswer(t('Sorry, something went wrong.') . ' ' . $exception->getMessage());
            $this->em->flush();

            return $chatMessage;
        }

        // validation? is possible validate response?

        $functionCallingChatMessage = $this->openAiFunctionCallingFacade->handleFunctionCalling($response, $chatMessage);

        if ($functionCallingChatMessage) {
            return $this->handleQuestion($functionCallingChatMessage);
        }

        $this->openAiMapper->mapOpenAiChatResponseToChatMessage($response, $chatMessage);

        $this->em->flush();

        return $chatMessage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return string|null
     */
    public function createVectorStore(VectorStore $vectorStore): ?string
    {
        try {
            $response = $this->openAiClient->createVectorStore($vectorStore);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return null;
        }

        return $response->id;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return bool
     */
    public function deleteVectorStore(VectorStore $vectorStore): bool
    {
        try {
            $response = $this->openAiClient->deleteVectorStore($vectorStore);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return false;
        }

        return $response->deleted;
    }

    /**
     * @return array<int, array{externalId: string, name: string}>
     */
    public function getAllVectorStoreResponses(): array
    {
        try {
            $response = $this->openAiClient->getVectorStoreList();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return [];
        }

        return array_map(fn ($data) => ['externalId' => $data->id, 'name' => $data->name], $response->data);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param array $payload
     */
    public function appendObjectToVectorStore(VectorStore $vectorStore, array $payload): void
    {
        try {
            $response = $this->openAiClient->uploadJson($payload);
            //            d($response);
            $vectorStoreResponse = $this->openAiClient->appendObjectToVectorStore($vectorStore, $response->id);
            //            d($vectorStoreResponse);
        } catch (Exception $exception) {
            //            d($exception);
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return;
        }

        //        d($response);
    }
}
