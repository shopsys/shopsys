<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use ReflectionClass;
use SplFileInfo;

/**
 * @implements \PhpCsFixer\Fixer\ConfigurableFixerInterface<array{namespace_prefixes: string[], excluded_namespace_prefixes: string[], excluded_class_names: string[]}, array{namespace_prefixes: string[], excluded_namespace_prefixes: string[], excluded_class_names: string[]}>
 */
final class ExtendedClassNameResolverFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    private const string RESOLVER_CLASS_NAME = 'Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver';
    private const string RESOLVER_SHORT_NAME = 'ExtendedClassNameResolver';

    private const array DEFAULT_NAMESPACE_PREFIXES = ['Shopsys\\'];

    private const array DEFAULT_EXCLUDED_NAMESPACE_PREFIXES = [
        'Shopsys\\Cli\\',
        'Shopsys\\CodingStandards\\',
        'Shopsys\\MonorepoTools\\',
        'Shopsys\\Releaser\\',
    ];

    private const array DEFAULT_EXCLUDED_CLASS_NAMES = [
        'Shopsys\FrameworkBundle\Component\Money\HiddenMoney',
        'Shopsys\FrameworkBundle\Component\Money\Money',
    ];

    public function __construct()
    {
        $this->configure([]);
    }

    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Static calls to framework classes must resolve the class name through ExtendedClassNameResolver, so the call lands in the project class when the project extends it',
            [new CodeSample(
                <<<'SAMPLE'
<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

class Product
{
    public function getSlug(): string
    {
        return TransformStringHelper::createFriendlyUrlSlug($this->name);
    }
}

SAMPLE,
            )],
        );
    }

    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOUBLE_COLON);
    }

    #[Override]
    public function isRisky(): bool
    {
        return true;
    }

    #[Override]
    public function getName(): string
    {
        return 'Shopsys/extended_class_name_resolver';
    }

    #[Override]
    public function getPriority(): int
    {
        return 20;
    }

    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return preg_match('/\.php$/ui', $file->getFilename()) === 1;
    }

    #[Override]
    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('namespace_prefixes', 'Namespaces whose classes are considered'))
                ->setAllowedTypes(['string[]'])
                ->setDefault(self::DEFAULT_NAMESPACE_PREFIXES)
                ->getOption(),
            (new FixerOptionBuilder('excluded_namespace_prefixes', 'Namespaces that are never touched, for example standalone tools without the framework'))
                ->setAllowedTypes(['string[]'])
                ->setDefault(self::DEFAULT_EXCLUDED_NAMESPACE_PREFIXES)
                ->getOption(),
            (new FixerOptionBuilder('excluded_class_names', 'Classes documented as not extendable although they are not final, for example because the framework itself extends them'))
                ->setAllowedTypes(['string[]'])
                ->setDefault(self::DEFAULT_EXCLUDED_CLASS_NAMES)
                ->getOption(),
        ]);
    }

    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $namespacesAnalyzer = new NamespacesAnalyzer();
        $namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();

        foreach (array_reverse($namespacesAnalyzer->getDeclarations($tokens)) as $namespace) {
            if ($namespace->isGlobalNamespace()) {
                continue;
            }

            $importedClassNamesByShortName = [];

            foreach ($namespaceUsesAnalyzer->getDeclarationsInNamespace($tokens, $namespace) as $use) {
                if ($use->isClass()) {
                    $importedClassNamesByShortName[$use->getShortName()] = $use->getFullName();
                }
            }

            $declaredClassNames = $this->findDeclaredClassNames($tokens, $namespace);
            $wrappedCount = 0;

            for ($index = $namespace->getScopeEndIndex(); $index > $namespace->getScopeStartIndex(); $index--) {
                if (!$tokens[$index]->isGivenKind(T_DOUBLE_COLON)) {
                    continue;
                }

                $classNameRange = $this->findStaticMethodCallClassNameRange($tokens, $index);

                if ($classNameRange === null) {
                    continue;
                }

                [$classNameStartIndex, $classNameEndIndex] = $classNameRange;
                $className = $this->resolveClassName(
                    $this->getClassNameContent($tokens, $classNameStartIndex, $classNameEndIndex),
                    $namespace,
                    $importedClassNamesByShortName,
                );

                if (!$this->shouldBeResolved($className, $declaredClassNames)) {
                    continue;
                }

                $this->wrapClassName($tokens, $classNameStartIndex, $classNameEndIndex);
                $wrappedCount++;
            }

            if ($wrappedCount > 0 && !in_array(self::RESOLVER_CLASS_NAME, $importedClassNamesByShortName, true)) {
                $this->addResolverImport($tokens, $namespace, $namespaceUsesAnalyzer);
            }
        }
    }

    /**
     * @return array{int, int}|null
     */
    private function findStaticMethodCallClassNameRange(Tokens $tokens, int $doubleColonIndex): ?array
    {
        $methodNameIndex = $tokens->getNextMeaningfulToken($doubleColonIndex);

        if ($methodNameIndex === null || !$tokens[$methodNameIndex]->isGivenKind(T_STRING)) {
            return null;
        }

        $openParenthesisIndex = $tokens->getNextMeaningfulToken($methodNameIndex);

        if ($openParenthesisIndex === null || !$tokens[$openParenthesisIndex]->equals('(')) {
            return null;
        }

        $classNameEndIndex = $tokens->getPrevMeaningfulToken($doubleColonIndex);

        if ($classNameEndIndex === null || !$tokens[$classNameEndIndex]->isGivenKind(T_STRING)) {
            return null;
        }

        if (in_array(strtolower($tokens[$classNameEndIndex]->getContent()), ['self', 'parent'], true)) {
            return null;
        }

        $classNameStartIndex = $classNameEndIndex;

        while (true) {
            $separatorIndex = $tokens->getPrevMeaningfulToken($classNameStartIndex);

            if ($separatorIndex === null || !$tokens[$separatorIndex]->isGivenKind(T_NS_SEPARATOR)) {
                break;
            }

            $segmentIndex = $tokens->getPrevMeaningfulToken($separatorIndex);

            if ($segmentIndex !== null && $tokens[$segmentIndex]->isGivenKind(T_NAMESPACE)) {
                return null;
            }

            $classNameStartIndex = $separatorIndex;

            if ($segmentIndex === null || !$tokens[$segmentIndex]->isGivenKind(T_STRING)) {
                break;
            }

            $classNameStartIndex = $segmentIndex;
        }

        $beforeClassNameIndex = $tokens->getPrevMeaningfulToken($classNameStartIndex);

        if ($beforeClassNameIndex !== null && $tokens[$beforeClassNameIndex]->isGivenKind([T_NEW, T_INSTANCEOF, T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON])) {
            return null;
        }

        return [$classNameStartIndex, $classNameEndIndex];
    }

    private function getClassNameContent(Tokens $tokens, int $startIndex, int $endIndex): string
    {
        $content = '';

        for ($index = $startIndex; $index <= $endIndex; $index++) {
            if (!$tokens[$index]->isWhitespace() && !$tokens[$index]->isComment()) {
                $content .= $tokens[$index]->getContent();
            }
        }

        return $content;
    }

    /**
     * @param array<string, string> $importedClassNamesByShortName
     */
    private function resolveClassName(
        string $name,
        NamespaceAnalysis $namespace,
        array $importedClassNamesByShortName,
    ): string {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $firstSegment = explode('\\', $name, 2)[0];
        $remainingSegments = substr($name, strlen($firstSegment));

        if (isset($importedClassNamesByShortName[$firstSegment])) {
            return $importedClassNamesByShortName[$firstSegment] . $remainingSegments;
        }

        return $namespace->getFullName() . '\\' . $name;
    }

    /**
     * @param string[] $declaredClassNames
     */
    private function shouldBeResolved(string $className, array $declaredClassNames): bool
    {
        if ($className === self::RESOLVER_CLASS_NAME || in_array($className, $declaredClassNames, true)) {
            return false;
        }

        if (in_array($className, $this->configuration['excluded_class_names'], true)) {
            return false;
        }

        if (!$this->hasAnyPrefix($className, $this->configuration['namespace_prefixes']) || $this->hasAnyPrefix($className, $this->configuration['excluded_namespace_prefixes'])) {
            return false;
        }

        return $this->isExtendableClass($className);
    }

    private function isExtendableClass(string $className): bool
    {
        if (!class_exists($className)) {
            return !interface_exists($className) && !enum_exists($className);
        }

        $reflectionClass = new ReflectionClass($className);

        return !$reflectionClass->isFinal() && !$reflectionClass->isEnum();
    }

    /**
     * @param string[] $prefixes
     */
    private function hasAnyPrefix(string $className, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($className, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function findDeclaredClassNames(Tokens $tokens, NamespaceAnalysis $namespace): array
    {
        $declaredClassNames = [];

        for ($index = $namespace->getScopeStartIndex(); $index <= $namespace->getScopeEndIndex(); $index++) {
            if (!$tokens[$index]->isGivenKind([T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
                continue;
            }

            $nameIndex = $tokens->getNextMeaningfulToken($index);

            if ($nameIndex !== null && $tokens[$nameIndex]->isGivenKind(T_STRING)) {
                $declaredClassNames[] = $namespace->getFullName() . '\\' . $tokens[$nameIndex]->getContent();
            }
        }

        return $declaredClassNames;
    }

    private function wrapClassName(Tokens $tokens, int $classNameStartIndex, int $classNameEndIndex): void
    {
        $tokens->insertAt($classNameEndIndex + 1, [
            new Token([T_DOUBLE_COLON, '::']),
            new Token([CT::T_CLASS_CONSTANT, 'class']),
            new Token(')'),
        ]);
        $tokens->insertAt($classNameStartIndex, [
            new Token([T_STRING, self::RESOLVER_SHORT_NAME]),
            new Token([T_DOUBLE_COLON, '::']),
            new Token([T_STRING, 'resolve']),
            new Token('('),
        ]);
    }

    /**
     * @return \PhpCsFixer\Tokenizer\Token[]
     */
    private function createResolverImportTokens(): array
    {
        $importTokens = [new Token([T_USE, 'use']), new Token([T_WHITESPACE, ' '])];

        foreach (explode('\\', self::RESOLVER_CLASS_NAME) as $position => $segment) {
            if ($position > 0) {
                $importTokens[] = new Token([T_NS_SEPARATOR, '\\']);
            }

            $importTokens[] = new Token([T_STRING, $segment]);
        }

        $importTokens[] = new Token(';');

        return $importTokens;
    }

    private function addResolverImport(
        Tokens $tokens,
        NamespaceAnalysis $namespace,
        NamespaceUsesAnalyzer $namespaceUsesAnalyzer,
    ): void {
        if ($namespace->getFullName() === substr(self::RESOLVER_CLASS_NAME, 0, -strlen('\\' . self::RESOLVER_SHORT_NAME))) {
            return;
        }

        $importTokens = $this->createResolverImportTokens();
        $uses = $namespaceUsesAnalyzer->getDeclarationsInNamespace($tokens, $namespace);

        if (count($uses) > 0) {
            $lastUseEndIndex = max(array_map(static fn ($use): int => $use->getEndIndex(), $uses));
            $tokens->insertAt($lastUseEndIndex + 1, [new Token([T_WHITESPACE, "\n"]), ...$importTokens]);

            return;
        }

        $tokens->insertAt($namespace->getEndIndex() + 1, [new Token([T_WHITESPACE, "\n\n"]), ...$importTokens]);
    }
}
