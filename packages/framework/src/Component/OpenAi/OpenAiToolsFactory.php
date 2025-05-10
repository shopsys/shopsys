<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Shopsys\FrameworkBundle\Model\Chat\Agent\Agent;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore;

class OpenAiToolsFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent $agent
     * @return array|null
     */
    public function getTools(Agent $agent): ?array
    {
        $tools = [];
        $availableVectorStores = array_filter($agent->getVectorStores(), fn (VectorStore $vectorStore) => $vectorStore->getExternalId() !== null);

        if (count($availableVectorStores) > 0) {
            $tools[] = $this->getFileSearch($availableVectorStores);
        }

        if (count($tools) > 0) {
            return $tools;
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore[] $vectorStores
     * @return array
     */
    protected function getFileSearch(array $vectorStores): array
    {
        return [
            'type' => 'file_search',
            'vector_store_ids' => array_map(fn (VectorStore $vectorStore) => $vectorStore->getExternalId(), $vectorStores),
        ];
    }
}
