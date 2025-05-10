<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use OpenAI\Client;

use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Files\CreateResponse as FilesCreateResponse;
use OpenAI\Responses\VectorStores\Files\VectorStoreFileResponse;
use OpenAI\Responses\VectorStores\VectorStoreDeleteResponse;
use OpenAI\Responses\VectorStores\VectorStoreListResponse;
use OpenAI\Responses\VectorStores\VectorStoreResponse;
use Shopsys\FrameworkBundle\Model\Chat\Chat;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore;

class OpenAiClient
{
    /**
     * @param \OpenAI\Client $client
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiRequestFactory $openAiRequestFactory
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly OpenAiRequestFactory $openAiRequestFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @return \OpenAI\Responses\Chat\CreateResponse
     */
    public function askChatQuestion(Chat $chat): CreateResponse
    {
        return $this->client->chat()->create($this->openAiRequestFactory->getOpenAiChatRequest($chat));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return \OpenAI\Responses\VectorStores\VectorStoreResponse
     */
    public function createVectorStore(VectorStore $vectorStore): VectorStoreResponse
    {
        return $this->client->vectorStores()->create($this->openAiRequestFactory->getCreateVectorStoreRequest($vectorStore));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return \OpenAI\Responses\VectorStores\VectorStoreDeleteResponse
     */
    public function deleteVectorStore(VectorStore $vectorStore): VectorStoreDeleteResponse
    {
        return $this->client->vectorStores()->delete($vectorStore->getExternalId());
    }

    /**
     * @return \OpenAI\Responses\VectorStores\VectorStoreListResponse
     */
    public function getVectorStoreList(): VectorStoreListResponse
    {
        return $this->client->vectorStores()->list(
            //            parameters: [
            //                'limit' => 10,
            //            ],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param string $fileIdentifier
     * @return \OpenAI\Responses\VectorStores\Files\VectorStoreFileResponse
     */
    public function appendObjectToVectorStore(
        VectorStore $vectorStore,
        string $fileIdentifier,
    ): VectorStoreFileResponse {
        return $this->client->vectorStores()->files()->create($vectorStore->getExternalId(), [
            'file_id' => $fileIdentifier,
        ]);
    }

    /**
     * @param array $payload
     * @return \OpenAI\Responses\Files\CreateResponse
     */
    public function uploadJson(array $payload): FilesCreateResponse
    {
        $fileName = $payload['dataObject'] . '-' . $payload[$payload['identifierKey']];

        unset($payload['dataObject'], $payload['identifierKey']);


        // serializace vstupu – většina API pro fine-tuning vyžaduje formát JSONL,
        // tj. jeden JSON objekt na řádku. Případnou konverzi si přizpůsobte.
        $jsonLine = json_encode($payload, JSON_THROW_ON_ERROR) . PHP_EOL;

        // 2) Vytvoříme pojmenovaný dočasný soubor
        $tmpPath = tempnam(sys_get_temp_dir(), 'jsonl_'); // např. /tmp/jsonl_abcd12
        $finalPath = $fileName . '.json';                 // chceme příponu .jsonl
        rename($tmpPath, $finalPath);

        // 3) Zapíšeme data
        file_put_contents($finalPath, $jsonLine);


        // 5) Otevřeme soubor znovu pro čtení
        $resourceForUpload = fopen($finalPath, 'r');

        $resource = $this->client->files()->upload([
            'purpose' => 'fine-tune',
            'file' => $resourceForUpload,
        ]);

        // 7) Úklid
        unlink($finalPath);

        return $resource;



        //        $response->id; // 'file-XjGxS3KTG0uNmNOK362iJua3'
        //        $response->object; // 'file'
        //        $response->bytes; // 140
        //        $response->createdAt; // 1613779657
        //        $response->filename; // 'mydata.jsonl'
        //        $response->purpose; // 'fine-tune'
        //        $response->status; // 'succeeded'
        //        $response->status_details; // null
    }
}
