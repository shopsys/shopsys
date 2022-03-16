<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer;

use PhpCsFixer\Fixer\FixerInterface;
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
     */
    public function isCandidate(Tokens $tokens)
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
    public function isRisky()
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
     */
    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        foreach ($this->fixers as $fixer) {
            $fixer->fix($file, $tokens);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'chained';
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(SplFileInfo $file)
    {
        foreach ($this->fixers as $fixer) {
            if ($fixer->supports($file)) {
                return true;
            }
        }

        return false;
    }
}
