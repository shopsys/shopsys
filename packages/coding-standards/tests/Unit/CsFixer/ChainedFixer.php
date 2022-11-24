<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

class ChainedFixer implements FixerInterface
{
    /**
     * @var \PhpCsFixer\Fixer\FixerInterface[]
     */
    private array $fixers = [];

    /**
     * @param \PhpCsFixer\Fixer\FixerInterface $fixer
     */
    public function registerFixer(FixerInterface $fixer): void
    {
        $this->fixers[] = $fixer;
    }

    /**
     * {@inheritDoc}
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    public function isCandidate(Tokens $tokens): bool
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->isCandidate($tokens)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isRisky(): bool
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->isRisky()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($this->fixers as $fixer) {
            $fixer->fix($file, $tokens);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'chained';
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(SplFileInfo $file): bool
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->supports($file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Chained fixer', []);
    }
}
