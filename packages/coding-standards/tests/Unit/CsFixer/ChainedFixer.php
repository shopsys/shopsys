<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer;

use Override;
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

    public function registerFixer(FixerInterface $fixer): void
    {
        $this->fixers[] = $fixer;
    }

    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->isCandidate($tokens)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function isRisky(): bool
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->isRisky()) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($this->fixers as $fixer) {
            $fixer->fix($file, $tokens);
        }
    }

    #[Override]
    public function getName(): string
    {
        return 'chained';
    }

    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->supports($file)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Chained fixer', []);
    }
}
