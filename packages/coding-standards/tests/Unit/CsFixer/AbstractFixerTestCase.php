<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tests\Test\Assert\AssertTokensTrait;
use PhpCsFixer\Tests\Test\IsIdenticalConstraint;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

abstract class AbstractFixerTestCase extends TestCase
{
    use IsIdenticalConstraint;
    use AssertTokensTrait;

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
                self::createIsIdenticalStringConstraint($expected)
            );
            static::assertTrue($tokens->isChanged(), 'Tokens collection built on input code must be marked as changed after fixing.');

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
            self::createIsIdenticalStringConstraint($expected)
        );
        static::assertFalse($tokens->isChanged(), 'Tokens collection built on expected code must not be marked as changed after fixing.');
    }
}
