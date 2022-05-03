<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Tests\CodingStandards\Unit\CsFixer\Constraint\IsIdenticalString;
use function is_string;

abstract class AbstractFixerTestCase extends TestCase
{
    /**
     * @return \PhpCsFixer\Fixer\FixerInterface
     */
    abstract protected function createFixerService(): FixerInterface;

    /**
     * @return iterable
     */
    abstract public function getTestingFiles(): iterable;

    /**
     * @param string $expectedFilePath
     * @param string|null $inputFilePath
     * @dataProvider getTestingFiles
     */
    public function testRegisteredFiles(string $expectedFilePath, ?string $inputFilePath = null): void
    {
        $file = new SplFileInfo($inputFilePath ?? $expectedFilePath);

        $expected = file_get_contents($expectedFilePath);

        $fixer = $this->createFixerService();

        if ($inputFilePath !== null) {
            $input = file_get_contents($inputFilePath);
            Tokens::clearCache();
            $tokens = Tokens::fromCode($input);

            if (!$fixer->isCandidate($tokens)) {
                return;
            }

            $fixer->fix($file, $tokens);

            static::assertThat(
                $tokens->generateCode(),
                new IsIdenticalString($expected)
            );
            static::assertTrue(
                $tokens->isChanged(),
                'Tokens collection built on input code must be marked as changed after fixing.'
            );

            $tokens->clearEmptyTokens();

            Tokens::clearCache();
            $expectedTokens = Tokens::fromCode($expected);

            static::assertTokens($expectedTokens, $tokens);
        }

        Tokens::clearCache();
        $tokens = Tokens::fromCode($expected);

        if (!$fixer->isCandidate($tokens)) {
            return;
        }

        $fixer->fix($file, $tokens);

        static::assertThat(
            $tokens->generateCode(),
            new IsIdenticalString($expected)
        );
        static::assertFalse(
            $tokens->isChanged(),
            'Tokens collection built on expected code must not be marked as changed after fixing.'
        );
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $expectedTokens
     * @param \PhpCsFixer\Tokenizer\Tokens $inputTokens
     */
    private static function assertTokens(Tokens $expectedTokens, Tokens $inputTokens): void
    {
        foreach ($expectedTokens as $index => $expectedToken) {
            if (!isset($inputTokens[$index])) {
                static::fail(
                    sprintf(
                        "The token at index %d must be:\n%s, but is not set in the input collection.",
                        $index,
                        $expectedToken->toJson()
                    )
                );
            }

            $inputToken = $inputTokens[$index];

            static::assertTrue(
                $expectedToken->equals($inputToken),
                sprintf(
                    "The token at index %d must be:\n%s,\ngot:\n%s.",
                    $index,
                    $expectedToken->toJson(),
                    $inputToken->toJson()
                )
            );

            $expectedTokenKind = $expectedToken->isArray() ? $expectedToken->getId() : $expectedToken->getContent();
            static::assertTrue(
                $inputTokens->isTokenKindFound($expectedTokenKind),
                sprintf(
                    'The token kind %s (%s) must be found in tokens collection.',
                    $expectedTokenKind,
                    is_string($expectedTokenKind) ? $expectedTokenKind : Token::getNameForId($expectedTokenKind)
                )
            );
        }

        static::assertSame(
            $expectedTokens->count(),
            $inputTokens->count(),
            'Both collections must have the same length.'
        );
    }
}
