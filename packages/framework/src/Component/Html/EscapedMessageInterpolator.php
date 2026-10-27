<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Html;

use Twig\Markup;

class EscapedMessageInterpolator
{
    /**
     * @param string|\Twig\Markup $message a string is treated as plain text and escaped, \Twig\Markup as HTML
     * @param array<string, string|int|\Twig\Markup|null> $parameters placeholders replaced in the message by escaped values
     */
    public function createHtml(string|Markup $message, array $parameters = []): Markup
    {
        $messageHtml = $message instanceof Markup ? (string)$message : $this->escape($message);

        return new Markup($this->interpolate($messageHtml, $parameters), 'UTF-8');
    }

    /**
     * @param array<string, string|int|\Twig\Markup|null> $parameters a \Twig\Markup value is already HTML, so it is inserted unescaped
     */
    public function interpolate(string $messageHtml, array $parameters): string
    {
        return strtr($messageHtml, array_map(
            fn (string|int|Markup|null $value): string => $value instanceof Markup ? (string)$value : $this->escape($value),
            $parameters,
        ));
    }

    public function escape(string|int|null $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
