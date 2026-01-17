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

class FinalFormTypeFixer implements FixerInterface
{
    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Form types extending AbstractType or AbstractTypeExtension must be final.',
            [],
        );
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

        // Process each namespace (including global namespace)
        foreach ($namespaces as $namespace) {
            $this->fixNamespace($tokens, $namespace);
        }
    }

    private function fixNamespace(Tokens $tokens, NamespaceAnalysis $namespace): void
    {
        $usesAnalyzer = new NamespaceUsesAnalyzer();
        $uses = $usesAnalyzer->getDeclarationsInNamespace($tokens, $namespace);

        // Process tokens in reverse order to avoid index shifting
        for ($index = $namespace->getScopeEndIndex(); $index >= $namespace->getScopeStartIndex(); --$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(T_CLASS)) {
                continue;
            }

            // Check if this is an abstract class or already final
            $prevMeaningfulIndex = $tokens->getPrevMeaningfulToken($index);

            if ($prevMeaningfulIndex !== null) {
                $prevToken = $tokens[$prevMeaningfulIndex];

                if ($prevToken->isGivenKind([T_ABSTRACT, T_FINAL])) {
                    continue;
                }
            }

            // Find extends keyword after class declaration
            $extendsIndex = $tokens->getNextTokenOfKind($index, [[T_EXTENDS]]);

            if ($extendsIndex === null) {
                continue;
            }

            // Get the parent class name
            $parentClassIndex = $tokens->getNextMeaningfulToken($extendsIndex);

            if ($parentClassIndex === null) {
                continue;
            }

            $parentName = $this->getClassNameFromTokens($tokens, $parentClassIndex);
            $resolvedParentName = $this->resolveClassNameWithUses($parentName, $uses);

            if (!$this->isFormTypeClass($resolvedParentName)) {
                continue;
            }

            // Insert "final" before class keyword
            $tokens->insertAt($index, new Token([T_FINAL, 'final']));
            $tokens->insertAt($index + 1, new Token([T_WHITESPACE, ' ']));
        }
    }

    #[Override]
    public function getName(): string
    {
        return 'Shopsys/final_form_type';
    }

    #[Override]
    public function getPriority(): int
    {
        // Before native FinalClassFixer
        return (new FinalClassFixer())->getPriority() + 1;
    }

    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    private function getClassNameFromTokens(Tokens $tokens, int $startIndex): string
    {
        $className = '';
        $index = $startIndex;

        // Collect all parts of the class name (including namespace separators)
        while ($index !== null && $tokens[$index]->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
            $className .= $tokens[$index]->getContent();
            $index = $tokens->getNextMeaningfulToken($index);
        }

        return $className;
    }

    /**
     * Resolve class name using use statements
     *
     * @param \PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis[] $uses
     */
    private function resolveClassNameWithUses(string $className, array $uses): string
    {
        // Already fully qualified - return as is
        if (str_starts_with($className, '\\')) {
            return $className;
        }

        // Check for exact match in use statements
        foreach ($uses as $use) {
            if ($className === $use->getShortName()) {
                return $use->getFullName();
            }
        }

        // Not found in imports - return as is (could be relative to current namespace)
        return $className;
    }

    private function isFormTypeClass(string $className): bool
    {
        return in_array($className, [
            'AbstractType',
            'AbstractTypeExtension',
            'Symfony\Component\Form\AbstractType',
            'Symfony\Component\Form\AbstractTypeExtension',
            '\Symfony\Component\Form\AbstractType',
            '\Symfony\Component\Form\AbstractTypeExtension',
        ], true);
    }
}
