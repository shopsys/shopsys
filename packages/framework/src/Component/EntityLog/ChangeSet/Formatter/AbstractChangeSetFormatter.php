<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

abstract class AbstractChangeSetFormatter
{
    protected function formatCode(mixed $value): string
    {
        return sprintf(
            '<code>%s</code>',
            htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    /**
     * @param string[] $formattedItems already escaped HTML
     */
    protected function formatIndentedList(array $formattedItems): string
    {
        return sprintf(
            '<ul class="list-unstyled ps-3 mb-0">%s</ul>',
            implode('', array_map(static fn (string $formattedItem) => sprintf('<li>%s</li>', $formattedItem), $formattedItems)),
        );
    }

    /**
     * Only escaped values may be passed to the translator - the result is rendered as raw HTML
     * and a translation referencing raw change values would inject unescaped customer input
     */
    protected function formatFromToChanges(mixed $oldReadableValue, mixed $newReadableValue): string
    {
        return t('from %oldReadableValue% to %newReadableValue%', [
            '%oldReadableValue%' => $this->formatCode($oldReadableValue),
            '%newReadableValue%' => $this->formatCode($newReadableValue),
        ]);
    }
}
