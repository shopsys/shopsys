<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Html;

class HtmlContentProcessor
{
    protected const string NOOPENER = 'noopener';

    protected const string LINK_OPENING_TAG_PATTERN = '~(<a\b)((?:"[^"]*"|\'[^\']*\'|[^>"\'])*?)(\s*/?>)~i';

    protected const string BLANK_TARGET_ATTRIBUTE_PATTERN
        = '~(?:^|\s)target\s*=\s*(?:"_blank"|\'_blank\'|_blank(?=[\s/]|$))~i';

    protected const string REL_ATTRIBUTE_PATTERN = '~(?:^|\s)rel\s*=~i';

    protected const string QUOTED_REL_ATTRIBUTE_PATTERN = '~((?:^|\s)rel\s*=\s*(["\']))(.*?)\2~is';

    public function process(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        return preg_replace_callback(
            static::LINK_OPENING_TAG_PATTERN,
            fn (array $matches): string => $matches[1] . $this->addRelNoopenerToLinkOpeningInNewTab($matches[2]) . $matches[3],
            $html,
        ) ?? $html;
    }

    /**
     * @param string $attributes everything between the tag name and the closing bracket of a single <a> tag
     */
    protected function addRelNoopenerToLinkOpeningInNewTab(string $attributes): string
    {
        if (preg_match(static::BLANK_TARGET_ATTRIBUTE_PATTERN, $attributes) !== 1) {
            return $attributes;
        }

        if (preg_match(static::REL_ATTRIBUTE_PATTERN, $attributes) !== 1) {
            return $attributes . ' rel="' . static::NOOPENER . '"';
        }

        return preg_replace_callback(
            static::QUOTED_REL_ATTRIBUTE_PATTERN,
            fn (array $matches): string => $matches[1] . $this->mergeNoopenerIntoRelValue($matches[3]) . $matches[2],
            $attributes,
            1,
        ) ?? $attributes;
    }

    /**
     * @param string $relValue the value of a single rel attribute, e.g. "nofollow noreferrer"
     */
    protected function mergeNoopenerIntoRelValue(string $relValue): string
    {
        $relValues = (array)preg_split('~\s+~', trim($relValue), -1, PREG_SPLIT_NO_EMPTY);

        if (array_any($relValues, fn ($existingRelValue) => strcasecmp((string)$existingRelValue, static::NOOPENER) === 0)) {
            return $relValue;
        }

        $relValues[] = static::NOOPENER;

        return implode(' ', $relValues);
    }
}
