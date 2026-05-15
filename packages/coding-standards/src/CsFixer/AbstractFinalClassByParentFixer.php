<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

abstract class AbstractFinalClassByParentFixer implements FixerInterface
{
    abstract protected function getDescription(): string;

    /**
     * @return string[]
     */
    abstract protected function getMatchingParentClasses(): array;

    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition($this->getDescription(), []);
    }

    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_EXTENDS);
    }

    #[Override]
    public function isRisky(): bool
    {
        return false;
    }

    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $namespacesAnalyzer = new NamespacesAnalyzer();
        $namespaces = $namespacesAnalyzer->getDeclarations($tokens);

        foreach ($namespaces as $namespace) {
            $this->fixNamespace($tokens, $namespace);
        }
    }

    #[Override]
    public function getPriority(): int
    {
        return (new FinalClassFixer())->getPriority() + 1;
    }

    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    protected function fixNamespace(Tokens $tokens, NamespaceAnalysis $namespace): void
    {
        $usesAnalyzer = new NamespaceUsesAnalyzer();
        $uses = $usesAnalyzer->getDeclarationsInNamespace($tokens, $namespace);

        for ($index = $namespace->getScopeEndIndex(); $index >= $namespace->getScopeStartIndex(); --$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(T_CLASS)) {
                continue;
            }

            if ($this->hasClassModifier($tokens, $index, [T_ABSTRACT, T_FINAL])) {
                continue;
            }

            $extendsIndex = $tokens->getNextTokenOfKind($index, [[T_EXTENDS]]);

            if ($extendsIndex === null) {
                continue;
            }

            $parentClassIndex = $tokens->getNextMeaningfulToken($extendsIndex);

            if ($parentClassIndex === null) {
                continue;
            }

            $parentName = $this->getClassNameFromTokens($tokens, $parentClassIndex);
            $resolvedParentName = $this->resolveClassNameWithUses($parentName, $uses);

            if (!in_array($resolvedParentName, $this->getMatchingParentClasses(), true)) {
                continue;
            }

            $tokens->insertAt($index, new Token([T_FINAL, 'final']));
            $tokens->insertAt($index + 1, new Token([T_WHITESPACE, ' ']));
        }
    }

    /**
     * @param int[] $modifiers
     */
    protected function hasClassModifier(Tokens $tokens, int $classIndex, array $modifiers): bool
    {
        $prevIndex = $tokens->getPrevMeaningfulToken($classIndex);

        while ($prevIndex !== null && $tokens[$prevIndex]->isGivenKind(T_READONLY)) {
            $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
        }

        return $prevIndex !== null && $tokens[$prevIndex]->isGivenKind($modifiers);
    }

    protected function getClassNameFromTokens(Tokens $tokens, int $startIndex): string
    {
        $className = '';
        $index = $startIndex;

        while ($index !== null && $tokens[$index]->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
            $className .= $tokens[$index]->getContent();
            $index = $tokens->getNextMeaningfulToken($index);
        }

        return $className;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis[] $uses
     */
    protected function resolveClassNameWithUses(string $className, array $uses): string
    {
        if (str_starts_with($className, '\\')) {
            return $className;
        }

        foreach ($uses as $use) {
            if ($className === $use->getShortName()) {
                return $use->getFullName();
            }
        }

        return $className;
    }
}
