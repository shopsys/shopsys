<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Dto;

class ChatRequest
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatMessage[] $messages (včetně system promptu, pokud je potřeba)
     * @param string|null $model
     * @param float $temperature
     * @param int $maxTokens
     * @param array $functions
     */
    public function __construct(
        public array $messages,
        public ?string $model = 'gpt-4o-mini',
        public float $temperature = 0.7,
        public int $maxTokens = 1024,
        public array $functions = [],
    ) {
    }
}
