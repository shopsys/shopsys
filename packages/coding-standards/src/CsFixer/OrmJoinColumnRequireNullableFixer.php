<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class OrmJoinColumnRequireNullableFixer implements FixerInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            '#[ORM\ManyToOne] and #[ORM\OneToOne] attributes must have defined nullable option in #[ORM\JoinColumn]',
            [new CodeSample(
                <<<'SAMPLE'
/**
  * @var \StdObject
  */
#[ORM\ManyToOne(targetEntity: StdObject::class)]
private $foo;
SAMPLE,
            ), new CodeSample(
                <<<'SAMPLE'
/**
  * @var \StdObject
  */
#[ORM\OneToOne(targetEntity: StdObject::class)]
private $foo;
SAMPLE,
            )],
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isRisky(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(T_VARIABLE)) {
                continue;
            }

            $propertyInfo = $this->analyzeProperty($tokens, $index);

            if ($propertyInfo === null || !$propertyInfo['hasRelation'] || $propertyInfo['hasNullable']) {
                continue;
            }

            if ($propertyInfo['joinColumnIndex'] !== null) {
                $this->addNullableToExistingJoinColumn($tokens, $propertyInfo['joinColumnIndex']);
            } else {
                $this->addNewJoinColumnAttribute($tokens, $propertyInfo['lastAttributeEnd']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'Shopsys/orm_join_column_require_nullable';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return preg_match('/\.php$/ui', $file->getFilename()) === 1;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $propertyIndex
     * @return array{hasRelation: bool, hasNullable: bool, joinColumnIndex: int|null, lastAttributeEnd: int|null}|null
     */
    private function analyzeProperty(Tokens $tokens, int $propertyIndex): ?array
    {
        $result = [
            'hasRelation' => false,
            'hasNullable' => false,
            'joinColumnIndex' => null,
            'lastAttributeEnd' => null,
        ];

        $index = $this->findAttributesStart($tokens, $propertyIndex);

        if ($index === null) {
            return null;
        }

        while ($index > 0 && $tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
            $attrStart = $this->findMatchingAttributeOpen($tokens, $index);

            if ($attrStart === null) {
                break;
            }

            $content = $this->getTokensContent($tokens, $attrStart, $index);

            if ($this->isRelationAttribute($content)) {
                $result['hasRelation'] = true;
            }

            if ($this->isJoinColumnAttribute($content)) {
                $result['joinColumnIndex'] = $attrStart;
                $result['hasNullable'] = $this->hasNullableParam($content);
            }

            if ($result['lastAttributeEnd'] === null) {
                $result['lastAttributeEnd'] = $index;
            }

            $index = $tokens->getPrevMeaningfulToken($attrStart);
        }

        return $result;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $propertyIndex
     * @return int|null
     */
    private function findAttributesStart(Tokens $tokens, int $propertyIndex): ?int
    {
        $index = $tokens->getPrevMeaningfulToken($propertyIndex);

        // Skip type hint
        while ($tokens[$index]->isGivenKind([T_STRING, T_NS_SEPARATOR, CT::T_NULLABLE_TYPE])) {
            $index = $tokens->getPrevMeaningfulToken($index);
        }

        // Skip visibility
        if ($tokens[$index]->isGivenKind([T_PRIVATE, T_PROTECTED, T_PUBLIC])) {
            $index = $tokens->getPrevMeaningfulToken($index);
        }

        return $tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE) ? $index : null;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $closeIndex
     * @return int|null
     */
    private function findMatchingAttributeOpen(Tokens $tokens, int $closeIndex): ?int
    {
        $depth = 1;

        for ($i = $closeIndex - 1; $i > 0 && $depth > 0; $i--) {
            if ($tokens[$i]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
                $depth++;
            } elseif ($tokens[$i]->isGivenKind(T_ATTRIBUTE)) {
                $depth--;

                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $start
     * @param int $end
     * @return string
     */
    private function getTokensContent(Tokens $tokens, int $start, int $end): string
    {
        $content = '';

        for ($i = $start; $i <= $end; $i++) {
            $content .= $tokens[$i]->getContent();
        }

        return $content;
    }

    /**
     * @param string $content
     * @return bool
     */
    private function isRelationAttribute(string $content): bool
    {
        return preg_match('~ORM\\\\(ManyToOne|OneToOne)\b~', $content) === 1;
    }

    /**
     * @param string $content
     * @return bool
     */
    private function isJoinColumnAttribute(string $content): bool
    {
        return preg_match('~ORM\\\\JoinColumn\b~', $content) === 1;
    }

    /**
     * @param string $content
     * @return bool
     */
    private function hasNullableParam(string $content): bool
    {
        return preg_match('~\bnullable\s*:~', $content) === 1;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $attrStart
     */
    private function addNullableToExistingJoinColumn(Tokens $tokens, int $attrStart): void
    {
        $openParen = $tokens->getNextTokenOfKind($attrStart, ['(']);
        $closeParen = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParen);

        $isMultiline = $this->isMultiline($tokens, $openParen, $closeParen);
        $hasParams = $this->hasExistingParams($tokens, $openParen, $closeParen);

        if ($isMultiline) {
            $indent = $this->detectIndent($tokens, $openParen, $closeParen);
            $insertTokens = [
                new Token([T_WHITESPACE, "\n" . $indent]),
                new Token([CT::T_NAMED_ARGUMENT_NAME, 'nullable']),
                new Token([CT::T_NAMED_ARGUMENT_COLON, ':']),
                new Token([T_WHITESPACE, ' ']),
                new Token([T_STRING, 'false']),
                new Token(','),
            ];
        } else {
            $insertTokens = [
                new Token([CT::T_NAMED_ARGUMENT_NAME, 'nullable']),
                new Token([CT::T_NAMED_ARGUMENT_COLON, ':']),
                new Token([T_WHITESPACE, ' ']),
                new Token([T_STRING, 'false']),
            ];

            if ($hasParams) {
                $insertTokens[] = new Token(',');
                $insertTokens[] = new Token([T_WHITESPACE, ' ']);
            }
        }

        $tokens->insertAt($openParen + 1, $insertTokens);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $lastAttrEnd
     */
    private function addNewJoinColumnAttribute(Tokens $tokens, int $lastAttrEnd): void
    {
        $indent = $this->detectIndentAfterAttribute($tokens, $lastAttrEnd);

        $insertTokens = [
            new Token([T_WHITESPACE, "\n" . $indent]),
            new Token([T_ATTRIBUTE, '#[']),
            new Token([T_STRING, 'ORM']),
            new Token([T_NS_SEPARATOR, '\\']),
            new Token([T_STRING, 'JoinColumn']),
            new Token('('),
            new Token([CT::T_NAMED_ARGUMENT_NAME, 'nullable']),
            new Token([CT::T_NAMED_ARGUMENT_COLON, ':']),
            new Token([T_WHITESPACE, ' ']),
            new Token([T_STRING, 'false']),
            new Token(')'),
            new Token([CT::T_ATTRIBUTE_CLOSE, ']']),
        ];

        $tokens->insertAt($lastAttrEnd + 1, $insertTokens);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isMultiline(Tokens $tokens, int $start, int $end): bool
    {
        for ($i = $start; $i <= $end; $i++) {
            if (str_contains($tokens[$i]->getContent(), "\n")) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $openParen
     * @param int $closeParen
     * @return bool
     */
    private function hasExistingParams(Tokens $tokens, int $openParen, int $closeParen): bool
    {
        for ($i = $openParen + 1; $i < $closeParen; $i++) {
            if (!$tokens[$i]->isWhitespace() && !$tokens[$i]->isComment()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $openParen
     * @param int $closeParen
     * @return string
     */
    private function detectIndent(Tokens $tokens, int $openParen, int $closeParen): string
    {
        for ($i = $openParen + 1; $i < $closeParen; $i++) {
            if (preg_match('/\n(\s+)/', $tokens[$i]->getContent(), $matches)) {
                return $matches[1];
            }
        }

        return '        ';
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $attrEnd
     * @return string
     */
    private function detectIndentAfterAttribute(Tokens $tokens, int $attrEnd): string
    {
        $next = $attrEnd + 1;

        if (isset($tokens[$next]) && $tokens[$next]->isWhitespace()) {
            if (preg_match('/\n(\s+)/', $tokens[$next]->getContent(), $matches)) {
                return $matches[1];
            }
        }

        return '    ';
    }
}
