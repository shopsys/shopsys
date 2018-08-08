<?php

namespace Shopsys\FrameworkBundle\Component\String;

class TsqueryFactory
{
    /**
     * @param string|null $searchText
     */
    public function getTsqueryWithAndConditions($searchText): string
    {
        $tokens = $this->splitToTokens($searchText);

        return implode(' & ', $tokens);
    }

    /**
     * @param string|null $searchText
     */
    public function getTsqueryWithAndConditionsAndPrefixMatchForLastWord($searchText): string
    {
        $tokens = $this->splitToTokensWithPrefixMatchForLastToken($searchText);

        return implode(' & ', $tokens);
    }

    /**
     * @param string|null $searchText
     */
    public function getTsqueryWithOrConditions($searchText): string
    {
        $tokens = $this->splitToTokens($searchText);

        return implode(' | ', $tokens);
    }

    /**
     * @param string|null $searchText
     */
    public function getTsqueryWithOrConditionsAndPrefixMatchForLastWord($searchText): string
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

    /**
     * @param string|null $searchText
     */
    public function isValidSearchText($searchText): bool
    {
        return count($this->splitToTokens($searchText)) > 0;
    }

    /**
     * @param string|null $searchText
     * @return string[]
     */
    private function splitToTokens($searchText): array
    {
        return preg_split(
            '/[^\w\/-]+/ui',
            $searchText,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }
}
