<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

use Nette\Utils\Strings;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use Shopsys\CodingStandards\Helper\PhpToDocTypeTransformer;
use Shopsys\CodingStandards\Helper\ShopsysFixerNaming;
use SplFileInfo;
use Symplify\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;

/**
 * Some code used from:
 * - @see \PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer
 * - @see \PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer
 *
 * Inspiration:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/fbca90cc5837b26996d41f02b4ba5c759943c8fa
 */
abstract class AbstractMissingAnnotationsFixer implements FixerInterface, DefinedFixerInterface
{
    /**
     * @var WhitespacesFixerConfig
     */
    protected $whitespacesFixerConfig;

    /**
     * @var FunctionsAnalyzer
     */
    protected $functionsAnalyzer;

    /**
     * @var IndentDetector
     */
    private $indentDetector;

    /**
     * @var PhpToDocTypeTransformer
     */
    protected $phpToDocTypeTransformer;

    /**
     * @param \PhpCsFixer\WhitespacesFixerConfig $whitespacesFixerConfig
     */
    public function __construct(WhitespacesFixerConfig $whitespacesFixerConfig)
    {
        $this->whitespacesFixerConfig = $whitespacesFixerConfig;
        $this->functionsAnalyzer = new FunctionsAnalyzer();
        $this->indentDetector = new IndentDetector($whitespacesFixerConfig);
        $this->phpToDocTypeTransformer = new PhpToDocTypeTransformer();
    }

    /**
     * @param \SplFileInfo $file
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     */
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $limit = $tokens->count() - 1;

        for ($index = $limit; $index > 0; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind(T_FUNCTION)) {
                continue;
            }

            if ($this->shouldSkipFunctionToken($tokens, $index)) {
                continue;
            }

            $docToken = $this->getDocToken($tokens, $index);
            if ($docToken && $this->shouldSkipDocToken($docToken)) {
                continue;
            }

            $this->processFunctionToken($tokens, $index, $docToken);
        }
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @param \PhpCsFixer\Tokenizer\Token|null $docToken
     */
    abstract protected function processFunctionToken(Tokens $tokens, int $index, ?Token $docToken): void;

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @return bool
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
    }

    /**
     * @return bool
     */
    public function isRisky(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return ShopsysFixerNaming::createFromClass(self::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param \SplFileInfo $file
     * @return bool
     */
    public function supports(SplFileInfo $file): bool
    {
        return (bool)Strings::match($file->getFilename(), '#\.php$#ui');
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return bool
     */
    protected function shouldSkipFunctionToken(Tokens $tokens, int $index): bool
    {
        $nextTokenPosition = $tokens->getNextMeaningfulToken($index);

        // anonymous functions
        return !$tokens[$nextTokenPosition]->isGivenKind(T_STRING);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Token $docToken
     * @return bool
     */
    protected function shouldSkipDocToken(Token $docToken): bool
    {
        if (stripos($docToken->getContent(), 'inheritdoc') !== false) {
            return true;
        }

        // ignore one-line phpdocs like `/** foo */`, as there is no place to put new annotations
        return strpos($docToken->getContent(), "\n") === false;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return string
     */
    protected function resolveIndent(Tokens $tokens, int $index): string
    {
        return str_repeat($this->whitespacesFixerConfig->getIndent(), $this->indentDetector->detectOnPosition($tokens, $index));
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return bool
     */
    protected function hasDoc(Tokens $tokens, int $index): bool
    {
        $docIndex = $this->getDocIndex($tokens, $index);
        return $tokens[$docIndex]->isGivenKind(T_DOC_COMMENT);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return int
     */
    protected function getDocIndex(Tokens $tokens, int $index): int
    {
        do {
            $index = $tokens->getPrevNonWhitespace($index);
        } while ($tokens[$index]->isGivenKind([T_STATIC, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FINAL, T_ABSTRACT, T_COMMENT]));

        return $index;
    }

    /**
     * @param Line[] $newLines
     * @param string $indent
     * @return string
     */
    protected function createDocContentFromLinesAndIndent(array $newLines, string $indent): string
    {
        $lines = [];
        $lines[] = '/**' . $this->whitespacesFixerConfig->getLineEnding();
        $lines = array_merge($lines, $newLines);
        $lines[] = $indent . ' */';

        return implode('', $lines);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Token $docToken
     * @param Line[] $newLines
     * @param int|null $offset
     * @return string
     */
    protected function createDocContentFromDocTokenAndNewLines(Token $docToken, array $newLines, ?int $offset = null): string
    {
        $doc = new DocBlock($docToken->getContent());
        $lines = $doc->getLines();

        $offset = $offset ?: (count($lines) - 1);

        array_splice(
            $lines,
            $offset,
            0,
            $newLines
        );

        return implode('', $lines);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return int
     */
    protected function getNewDocIndex(Tokens $tokens, int $index): int
    {
        for ($i = $index; $i > 0; --$i) {
            if ($this->isWhitespaceWithNewline($tokens, $i)) {
                return $i + 1;
            }
        }

        return $index;
    }

    /**
     * @param Tokens $tokens
     * @param int $index
     * @param Token $docToken
     * @param Line[] $newLines
     * @param int|null $lastParamLine
     */
    protected function updateDocWithLines(Tokens $tokens, int $index, Token $docToken, array $newLines, ?int $lastParamLine = null): void
    {
        $docBlockIndex = $this->getDocIndex($tokens, $index);
        $docContent = $this->createdocContentFromDocTokenAndNewLines($docToken, $newLines, $lastParamLine);

        $tokens[$docBlockIndex] = new Token([T_DOC_COMMENT, $docContent]);
    }

    /**
     * @param Tokens $tokens
     * @param int $index
     * @param Line[] $newLines
     * @param string $indent
     */
    protected function addDocWithLines(Tokens $tokens, int $index, array $newLines, string $indent): void
    {
        $docBlockIndex = $this->getNewDocIndex($tokens, $index);
        $docContent = $this->createDocContentFromLinesAndIndent($newLines, $indent);

        $tokens->insertAt($docBlockIndex, new Token([T_DOC_COMMENT, $docContent]));
        $whitespaceAfterDocblock = $this->whitespacesFixerConfig->getLineEnding() . $indent;
        $tokens->ensureWhitespaceAtIndex($docBlockIndex, 1, $whitespaceAfterDocblock);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return \PhpCsFixer\Tokenizer\Token|null
     */
    private function getDocToken(Tokens $tokens, int $index): ?Token
    {
        $docIndex = $this->getDocIndex($tokens, $index);
        $docToken = $tokens[$docIndex];

        if ($docToken->isGivenKind(T_DOC_COMMENT)) {
            return $docToken;
        }

        return null;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @return bool
     */
    private function isWhitespaceWithNewline(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->isWhitespace()) {
            return false;
        }

        $content = $tokens[$index]->getContent();

        return Strings::contains($content, $this->whitespacesFixerConfig->getLineEnding());
    }
}
