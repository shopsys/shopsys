<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Dto;

class ChatResponse
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatMessage[] $choices (většina providerů vrací pole návrhů)
     * @param int $promptTokens
     * @param int $completionTokens
     * @param string $providerModel
     */
    public function __construct(
        public array $choices,
        public int $promptTokens,
        public int $completionTokens,
        public string $providerModel,            // skutečně použitý model
    ) {
    }
}
