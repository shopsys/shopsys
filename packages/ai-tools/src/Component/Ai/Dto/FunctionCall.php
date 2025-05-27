<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Dto;

/**
 * Struktura „tool callu“ vracená LLM.
 */
class FunctionCall
{
    /**
     * @param string $name
     * @param array<string, string|int|float|bool|null> $arguments
     * @param string|null $content
     */
    public function __construct(
        public string $name,
        public array $arguments = [],
        public ?string $content = null, // pro případ, že provider vrátí i přirozený text
    ) {
    }
}
