<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Client;

use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRequest;
use Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore;

/**
 * Jednotné rozhraní pro všechny LLM poskytovatele.
 */
interface AIClientInterface
{
    /**
     * Provede chat completion.
     *
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRequest $request
     * @return \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatResponse
     */
    public function chat(ChatRequest $request): ChatResponse;

    /**
     * Vypočítá embeddingy pro dodaný text(-y).
     *
     * @param string[] $input
     * @return list<list<float>>  pole vektorů
     */
    public function embeddings(array $input): array;

    /**
     * Nahraje/aktualizuje soubory do konkrétního vector-store u poskytovatele.
     * Cílem je schovat provider-specific detaily.
     *
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param array<string, string> $files [souborId => absolutníCesta]
     */
    public function upsertFiles(VectorStore $vectorStore, array $files): void;

    /**
     * Semantické vyhledávání nad vector-store.
     *
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param string $query
     * @param int $limit
     * @return array<array{score: float, payload: mixed}>
     */
    public function search(VectorStore $vectorStore, string $query, int $limit = 5): array;
}
