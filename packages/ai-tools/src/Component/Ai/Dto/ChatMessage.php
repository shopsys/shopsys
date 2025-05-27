<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Dto;

class ChatMessage
{
    /**
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\ChatRoleEnum $role
     * @param string|null $content
     * @param string|null $name
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\FunctionCall|null $functionCall
     * @param \Shopsys\AiToolsBundle\Component\Ai\Dto\FunctionCall|null $functionCallResult
     */
    public function __construct(
        public ChatRoleEnum $role,
        public ?string $content,
        public ?string $name = null,
        public ?FunctionCall $functionCall = null,        // požadavek
        public ?FunctionCall $functionCallResult = null,  // výsledek
    ) {
    }
}
