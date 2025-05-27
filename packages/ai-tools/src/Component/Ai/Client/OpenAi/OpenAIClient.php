<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Client\OpenAi;

use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponse as SdkChatResponse;
use OpenAI\Responses\Chat\CreateResponseChoice;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use OpenAI\Responses\Files\CreateResponse as FilesCreateResponse;
use OpenAI\Responses\VectorStores\Files\VectorStoreFileResponse;
use OpenAI\Responses\VectorStores\Search\VectorStoreSearchResponseFile;
use OpenAI\Responses\VectorStores\VectorStoreDeleteResponse;
use OpenAI\Responses\VectorStores\VectorStoreListResponse;
use OpenAI\Responses\VectorStores\VectorStoreResponse;
use Shopsys\AiToolsBundle\Component\Ai\Client\AIClientInterface;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatMessage;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRequest;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRoleEnum;
use Shopsys\AiToolsBundle\Component\Ai\Dto\FunctionCall;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore;

final class OpenAIClient implements AIClientInterface
{
    /**
     * @param \OpenAI\Contracts\ClientContract $openai
     * @param \Shopsys\AiToolsBundle\Component\Ai\Client\OpenAi\OpenAiFunctionCallingFactory $openAiFunctionCallingFactory
     */
    public function __construct(
        private readonly ClientContract $openai,
        private readonly OpenAiFunctionCallingFactory $openAiFunctionCallingFactory,
    ) {
    }

    /* -------------------------------- chat() ------------------------------ */

    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRequest $request
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse
     */
    public function chat(ChatRequest $request): ChatResponse
    {
        $payload = [
            'model' => $request->model,
            'messages' => array_map(
                static function (ChatMessage $m): array {
                    $message = [
                        'role' => $m->role->value,
                        'content' => $m->content,
                    ];

                    if ($m->name) {
                        $message['name'] = $m->name;
                    }

                    return $message;
                },
                $request->messages,
            ),
            'temperature' => $request->temperature,
            'max_tokens' => $request->maxTokens,
        ];


        /* ---------- podpora „function calling“ (OpenAI tools) --------------- */
        if (count($request->functions) > 0) {
            $payload['tools'] = $this->openAiFunctionCallingFactory->getFunctions($request->functions);


            //            // explicitní volbu nástroje posíláme pouze pokud je nastavena
            //            if ($request->toolChoice !== null) {
            //                $payload['tool_choice'] = $request->toolChoice;
            //            }
        }

        /* -------------------------------------------------------------------- */


        $sdkResponse = $this->openai
            ->chat()
            ->create($payload);

        return $this->mapChatResponse($sdkResponse);
    }

    /**
     * @param \OpenAI\Responses\Chat\CreateResponse $sdkResponse
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse
     */
    private function mapChatResponse(SdkChatResponse $sdkResponse): ChatResponse
    {
        $choices = array_map(
            fn (CreateResponseChoice $choice) => new ChatMessage(
                role: ChatRoleEnum::ASSISTANT,
                content: $choice->message->content,
                functionCall: $this->mapFunctionCall($choice->message->toolCalls),
            ),
            $sdkResponse->choices,
        );

        return new ChatResponse(
            choices: $choices,
            promptTokens: $sdkResponse->usage->promptTokens,
            completionTokens: $sdkResponse->usage->completionTokens,
            providerModel: $sdkResponse->model,
        );
    }

    /**
     * @param \OpenAI\Responses\Chat\CreateResponseToolCall[] $functionCalls
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\FunctionCall|null
     */
    private function mapFunctionCall(array $functionCalls): ?FunctionCall
    {
        $functionCalls = array_filter($functionCalls, static fn (CreateResponseToolCall $toolCall) => $toolCall->type === 'function');

        if (count($functionCalls) > 0) {
            return array_map(fn (CreateResponseToolCall $functionCall): FunctionCall => new FunctionCall($functionCall->function->name, json_decode($functionCall->function->arguments, true)), $functionCalls)[0];
        }

        return null;
    }

    /* ------------------------------ embeddings() -------------------------- */

    /**
     * @param array $input
     * @return array
     */
    public function embeddings(array $input): array
    {
        /** @var \OpenAI\Responses\Embeddings\CreateResponse $sdk */
        $sdk = $this->openai->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $input,
        ]);

        return array_map(
            static fn ($data) => $data->embedding,
            $sdk->embeddings,
        );
    }

    /* ----------------------------- vector-store --------------------------- */

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param array $files
     */
    public function upsertFiles(VectorStore $vectorStore, array $files): void
    {
        foreach ($files as $fileId => $path) {
            $this->openai
                ->files()
                ->upload([
                    'purpose' => 'assistants',
                    'file' => fopen($path, 'r'),
                ]);
            // TODO: přiřazení k vector-store -> assistants()->files()->create() …
        }
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(VectorStore $vectorStore, string $query, int $limit = 5): array
    {
        $response = $this->openai
            ->vectorStores()
            ->search($vectorStore->getExternalId(), [
                'query' => $query,
                'max_num_results' => $limit,
                'filters' => [],
                'rewrite_query' => false,
            ]);

        return array_map(
            static fn (VectorStoreSearchResponseFile $match) => [
                'score' => $match->score,
                'payload' => $match->content,
            ],
            $response->data,
        );
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return \OpenAI\Responses\VectorStores\VectorStoreResponse
     */
    public function createVectorStore(VectorStore $vectorStore): VectorStoreResponse
    {
        return $this->openai->vectorStores()->create(
            [
                'name' => $vectorStore->getName(),
            ],
        );
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return \OpenAI\Responses\VectorStores\VectorStoreDeleteResponse
     */
    public function deleteVectorStore(VectorStore $vectorStore): VectorStoreDeleteResponse
    {
        return $this->openai->vectorStores()->delete($vectorStore->getExternalId());
    }

    /**
     * @return \OpenAI\Responses\VectorStores\VectorStoreListResponse
     */
    public function getVectorStoreList(): VectorStoreListResponse
    {
        return $this->openai->vectorStores()->list(
            //            parameters: [
            //                'limit' => 10,
            //            ],
        );
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param string $fileIdentifier
     * @return \OpenAI\Responses\VectorStores\Files\VectorStoreFileResponse
     */
    public function appendObjectToVectorStore(
        VectorStore $vectorStore,
        string $fileIdentifier,
    ): VectorStoreFileResponse {
        return $this->openai->vectorStores()->files()->create($vectorStore->getExternalId(), [
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

        $resource = $this->openai->files()->upload([
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
