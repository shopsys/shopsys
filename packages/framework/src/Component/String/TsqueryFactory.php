<?php

namespace Shopsys\FrameworkBundle\Component\String;

class TsqueryFactory
{
    public function getTsqueryWithAndConditions(?string $searchText): string
    {
        $tokens = $this->splitToTokens($searchText);

        return implode(' & ', $tokens);
    }

    public function getTsqueryWithAndConditionsAndPrefixMatchForLastWord(?string $searchText): string
    {
        $tokens = $this->splitToTokensWithPrefixMatchForLastToken($searchText);

        return implode(' & ', $tokens);
    }

    public function getTsqueryWithOrConditions(?string $searchText): string
    {
        $tokens = $this->splitToTokens($searchText);

        return implode(' | ', $tokens);
    }

    public function getTsqueryWithOrConditionsAndPrefixMatchForLastWord(?string $searchText): string
    {
        $tokens = $this->splitToTokensWithPrefixMatchForLastToken($searchText);

        return implode(' | ', $tokens);
    }

    private function splitToTokensWithPrefixMatchForLastToken($searchText)
    {
        $tokens = $this->splitToTokens($searchText);

        if (count($tokens)) {
            end($tokens);
            $lastKey = key($tokens);
            $tokens[$lastKey] = $tokens[$lastKey] . ':*';
        }

        return $tokens;
    }

    public function isValidSearchText(?string $searchText): bool
    {
        return count($this->splitToTokens($searchText)) > 0;
    }

    /**
     * @param string|null $searchText
     * @return string[]
     */
    private function splitToTokens(?string $searchText): array
    {
        return preg_split(
            '/[^\w\/-]+/ui',
            $searchText,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }
}
