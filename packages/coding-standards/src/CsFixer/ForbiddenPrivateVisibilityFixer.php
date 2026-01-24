<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOption;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Shopsys\CodingStandards\Exception\NamespaceNotFoundException;
use SplFileInfo;

final class ForbiddenPrivateVisibilityFixer implements ConfigurableFixerInterface
{
    private const OPTION_ANALYZED_NAMESPACE = 'analyzed_namespaces';

    /**
     * @var string[]
     */
    private array $analyzedNamespaces = [];

    #[Override]
    public function configure(?array $configuration = null): void
    {
        if ($configuration !== null) {
            $this->analyzedNamespaces = $this->extractNamespaces($configuration);
        }
    }

    #[Override]
    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver(
            new FixerOption(
                self::OPTION_ANALYZED_NAMESPACE,
                'Define analyzed namespace.',
            ),
        );
    }

    private function extractNamespaces(array $configuration): array
    {
        if (!array_key_exists(self::OPTION_ANALYZED_NAMESPACE, $configuration)) {
            return [];
        }

        if (!is_array($configuration[self::OPTION_ANALYZED_NAMESPACE])) {
            throw new InvalidFixerConfigurationException(
                $this->getName(),
                'Namespace configuration has to be an array',
            );
        }

        return $configuration[self::OPTION_ANALYZED_NAMESPACE];
    }

    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Properties and methods should be public or protected in defined namespace (if the class is not final).',
            [
                new CodeSample(
                    '<?php
namespace Some\Namespace;
class SomeClass
{
private $property;
}',
                ),
                new CodeSample(
                    '<?php
namespace Some\Namespace;
class SomeClass
{
private function method()
{
    ...
}
}',
                ),
            ],
        );
    }

    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return !$this->isFinalClass($tokens) && $tokens->isAnyTokenKindsFound([T_PRIVATE, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE]) && $this->checkNamespace($tokens);
    }

    private function checkNamespace(Tokens $tokens): bool
    {
        try {
            $namespace = $this->getNamespace($tokens);
        } catch (NamespaceNotFoundException $e) {
            return false;
        }

        foreach ($this->analyzedNamespaces as $analyzedNamespace) {
            if ($this->namespaceStartsWith($namespace, $analyzedNamespace)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Shopsys\CodingStandards\Exception\NamespaceNotFoundException
     */
    private function getNamespace(Tokens $tokens): string
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_NAMESPACE)) {
                continue;
            }

            $namespaceStartIndex = $tokens->getNextMeaningfulToken($index);
            $namespaceEndIndex = $tokens->getPrevMeaningfulToken($tokens->getNextTokenOfKind($index, [';']));

            return $tokens->generatePartialCode($namespaceStartIndex, $namespaceEndIndex);
        }

        throw new NamespaceNotFoundException('No namespace found');
    }

    private function namespaceStartsWith(string $fullNamespace, string $namespacePrefix): bool
    {
        return strncmp($fullNamespace, $namespacePrefix, strlen($namespacePrefix)) === 0;
    }

    private function isFinalClass(Tokens $tokens): bool
    {
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(T_CLASS)) {
                $prevIndex = $tokens->getPrevMeaningfulToken($index);

                if ($prevIndex !== null && $tokens[$prevIndex]->isGivenKind(T_FINAL)) {
                    return true;
                }
            }
        }

        return false;
    }

    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach (array_keys($tokens->findGivenKind(T_PRIVATE)) as $index) {
            $tokens[$index] = new Token([T_PROTECTED, 'protected']);
        }

        foreach (array_keys($tokens->findGivenKind(CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE)) as $index) {
            $tokens[$index] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, 'protected']);
        }
    }

    #[Override]
    public function isRisky(): bool
    {
        return true;
    }

    #[Override]
    public function getName(): string
    {
        return 'Shopsys/forbidden_private_visibility';
    }

    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return true;
    }
}
