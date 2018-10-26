<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Helper;

use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;

final class FqnNameResolver
{
    /**
     * @var NamespaceUsesAnalyzer
     */
    private $namespaceUsesAnalyzer;

    public function __construct()
    {
        $this->namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param string $className
     * @return string
     */
    public function resolve(Tokens $tokens, string $className): string
    {
        if ($className === '') {
            return '';
        }

        // probably not a class name, skip
        if (ctype_lower($className[0])) {
            return $className;
        }

        $namespaceUseAnalyses = $this->namespaceUsesAnalyzer->getDeclarationsFromTokens($tokens);
        foreach ($namespaceUseAnalyses as $namespaceUseAnalysis) {
            if ($className === $namespaceUseAnalysis->getShortName()) {
                return $namespaceUseAnalysis->getFullName();
            }
        }

        $namespaceTokens = $tokens->findGivenKind([T_NAMESPACE], 0);
        if (!count($namespaceTokens[T_NAMESPACE])) {
            return $className;
        }

        $namespaceToken = array_pop($namespaceTokens);
        reset($namespaceToken);

        $namespacePosition = (int)key($namespaceToken);
        $namespaceName = '';
        $position = $namespacePosition + 2;

        while ($tokens[$position]->isGivenKind([T_NS_SEPARATOR, T_STRING])) {
            $namespaceName .= $tokens[$position]->getContent();
            ++$position;
        }

        return $namespaceName . '\\' . $className;
    }
}
