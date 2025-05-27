<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Application;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\AiToolsBundle\Component\Ai\Client\AIClientFactory;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelApiSourceEnum;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore;

class AiVectorStoreFacade
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Client\AIClientFactory $clientFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly AIClientFactory $clientFactory,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return string|null
     */
    public function createVectorStore(VectorStore $vectorStore): ?string
    {
        try {
            $client = $this->clientFactory->getClientByApiSource(AiModelApiSourceEnum::OPENAI);
            $response = $client->createVectorStore($vectorStore);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return null;
        }

        return $response->id;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return bool
     */
    public function deleteVectorStore(VectorStore $vectorStore): bool
    {
        try {
            $client = $this->clientFactory->getClientByApiSource(AiModelApiSourceEnum::OPENAI);
            $response = $client->deleteVectorStore($vectorStore);
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
            $client = $this->clientFactory->getClientByApiSource(AiModelApiSourceEnum::OPENAI);
            $response = $client->getVectorStoreList();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return [];
        }

        return array_map(fn ($data) => ['externalId' => $data->id, 'name' => $data->name], $response->data);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param array $payload
     */
    public function appendObjectToVectorStore(VectorStore $vectorStore, array $payload): void
    {
        try {
            $client = $this->clientFactory->getClientByApiSource(AiModelApiSourceEnum::OPENAI);
            $response = $client->uploadJson($payload);
            //            d($response);
            $vectorStoreResponse = $client->appendObjectToVectorStore($vectorStore, $response->id);
            //            d($vectorStoreResponse);
        } catch (Exception $exception) {
            //            d($exception);
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return;
        }

        //        d($response);
    }
}
