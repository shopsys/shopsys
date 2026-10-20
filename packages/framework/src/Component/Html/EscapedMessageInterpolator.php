<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Html;

class EscapedMessageInterpolator
{
    /**
     * @param array<string, string|int|null> $parameters
     */
    public function interpolate(string $messageHtml, array $parameters): string
    {
        return strtr($messageHtml, array_map($this->escape(...), $parameters));
    }

    public function escape(string|int|null $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
