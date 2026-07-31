<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

trait AppendsHtmlAttributeTrait
{
    /**
     * Matches the indentation of the last attribute line, so that a tag written with one attribute per line
     * keeps that formatting instead of getting the new attribute glued to the last one
     */
    protected const string LAST_ATTRIBUTE_LINE_PATTERN = '@(\R)([ \t]*)[^\r\n]*$@';

    /**
     * @param string $attributes everything between the tag name and the closing bracket of a single tag
     * @param string $attribute the attribute to append, e.g. 'rel="noopener"'
     */
    protected function appendHtmlAttribute(string $attributes, string $attribute): string
    {
        if (preg_match(static::LAST_ATTRIBUTE_LINE_PATTERN, $attributes, $matches) === 1) {
            return $attributes . $matches[1] . $matches[2] . $attribute;
        }

        return $attributes . ' ' . $attribute;
    }
}
