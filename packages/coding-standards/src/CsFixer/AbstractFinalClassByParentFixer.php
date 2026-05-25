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
        return $tokens->isTokenKindFound(T_EXTENDS) || $tokens->isTokenKindFound(T_IMPLEMENTS);
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

            $classOpenIndex = $tokens->getNextTokenOfKind($index, ['{']);

            if ($classOpenIndex === null) {
                continue;
            }

            if (!$this->hasMatchingParent($tokens, $index, $classOpenIndex, $uses)) {
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

    /**
     * @param \PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis[] $uses
     */
    protected function hasMatchingParent(Tokens $tokens, int $classIndex, int $classOpenIndex, array $uses): bool
    {
        foreach ([T_EXTENDS, T_IMPLEMENTS] as $parentTokenKind) {
            $parentTokenIndex = $tokens->getNextTokenOfKind($classIndex, [[$parentTokenKind]]);

            if ($parentTokenIndex === null || $parentTokenIndex > $classOpenIndex) {
                continue;
            }

            foreach ($this->getParentNames($tokens, $parentTokenIndex, $classOpenIndex) as $parentName) {
                $resolvedParentName = $this->resolveClassNameWithUses($parentName, $uses);

                if (in_array($resolvedParentName, $this->getMatchingParentClasses(), true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    protected function getParentNames(Tokens $tokens, int $parentTokenIndex, int $classOpenIndex): array
    {
        $parentNames = [];
        $index = $tokens->getNextMeaningfulToken($parentTokenIndex);

        while ($index !== null && $index < $classOpenIndex) {
            if (!$this->isClassNameToken($tokens[$index])) {
                break;
            }

            $parentNames[] = $this->getClassNameFromTokens($tokens, $index);

            while ($index !== null && $index < $classOpenIndex && !$tokens[$index]->equals(',')) {
                $index = $tokens->getNextMeaningfulToken($index);
            }

            if ($index === null || $index >= $classOpenIndex) {
                break;
            }

            $index = $tokens->getNextMeaningfulToken($index);
        }

        return $parentNames;
    }

    protected function getClassNameFromTokens(Tokens $tokens, int $startIndex): string
    {
        $className = '';
        $index = $startIndex;

        while ($index !== null && $this->isClassNameToken($tokens[$index])) {
            $className .= $tokens[$index]->getContent();
            $index = $tokens->getNextMeaningfulToken($index);
        }

        return $className;
    }

    protected function isClassNameToken(Token $token): bool
    {
        return $token->isGivenKind([
            T_STRING,
            T_NS_SEPARATOR,
            T_NAME_FULLY_QUALIFIED,
            T_NAME_QUALIFIED,
            T_NAME_RELATIVE,
        ]);
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

            if (str_starts_with($className, $use->getShortName() . '\\')) {
                return $use->getFullName() . substr($className, strlen($use->getShortName()));
            }
        }

        return $className;
    }
}
