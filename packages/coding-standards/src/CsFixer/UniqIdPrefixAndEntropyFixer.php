<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class UniqIdPrefixAndEntropyFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Function `uniqid` must be called with prefix and entropy',
            [
                new CodeSample("<?php\nuniqid('id', true);\n"),
                new CodeSample("<?php\nuniqid();\n"),
            ],
            null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        for ($index = count($tokens) - 1; $index > 0; --$index) {
            if (!$tokens[$index]->equals([T_STRING, 'uniqid'], false)) {
                continue;
            }

            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }

            $argumentsIndices = $this->getArgumentIndices($tokens, $index);

            if (count($argumentsIndices) === 0) {
                $tokens->getNextTokenOfKind($index, ['(']);
                $tokens->insertAt($index + 2, [
                    new Token([T_CONSTANT_ENCAPSED_STRING, "'id'"]),
                    new Token(','),
                ]);
                $tokens->ensureWhitespaceAtIndex($index + 4, 0, ' ');
                $tokens->insertAt($index + 5, [
                    new Token([T_STRING, 'true']),
                ]);

                continue;
            }

            if (2 === \count($argumentsIndices)) {
                [$firstArgumentIndex, $secondArgumentIndex] = array_keys($argumentsIndices);

                // If the first argument is string we have nothing to do
//                if ($tokens[$firstArgumentIndex]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
//                    continue;
//                }
                // If the second argument is not string we cannot make a swap
//                if (!$tokens[$secondArgumentIndex]->isGivenKind(T_BOOL_CAST)) {
//                    continue;
//                }

                // collect tokens from first argument
                $firstArgumentEndIndex = $argumentsIndices[key($argumentsIndices)];
                /** @var Token[] $firstArgumentTokens */
                $firstArgumentTokens = [];
                for ($i = key($argumentsIndices); $i <= $firstArgumentEndIndex; ++$i) {
                    // first argument tokens
                    $firstArgumentTokens[] = clone $tokens[$i];
                    $tokens->clearAt($i);
                }

                if (count($firstArgumentTokens) > 0 && strlen($firstArgumentTokens[0]->getContent()) < 3) {
                    $tokens->insertAt($firstArgumentIndex, new Token([T_CONSTANT_ENCAPSED_STRING, "'id'"]));
                    $tokens->ensureWhitespaceAtIndex($firstArgumentIndex + 4, 0, ' ');
                }

                // insert above increased the second argument index
                ++$secondArgumentIndex;
                $tokens->clearAt($secondArgumentIndex);
                $tokens->ensureWhitespaceAtIndex($secondArgumentIndex + 1, 0, ' ');
                $tokens->insertAt($index + 2, [
                    new Token([T_STRING, 'true']),
                ]);
            }
        }
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $functionNameIndex
     * @return array<int, int> In the format: startIndex => endIndex
     */
    private function getArgumentIndices(Tokens $tokens, int $functionNameIndex): array
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        $openParenthesis = $tokens->getNextTokenOfKind($functionNameIndex, ['(']);
        $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis);

        $indices = [];

        foreach ($argumentsAnalyzer->getArguments($tokens, $openParenthesis, $closeParenthesis) as $startIndexCandidate => $endIndex) {
            $indices[$tokens->getNextMeaningfulToken($startIndexCandidate - 1)] = $tokens->getPrevMeaningfulToken($endIndex + 1);
        }

        return $indices;
    }
}
