<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ForbiddenDoctrineInheritanceSniff implements Sniff
{
    /**
     * @return int[]
     */
    #[Override]
    public function register(): array
    {
        return [T_CLASS];
    }

    /**
     * @param int $classPosition
     */
    #[Override]
    public function process(File $file, $classPosition): void
    {
        $tokens = $file->getTokens();

        $contentBeforeClass = '';

        for ($i = 0; $i < $classPosition; $i++) {
            $contentBeforeClass .= $tokens[$i]['content'];
        }

        if (preg_match('~#\[.*ORM.*InheritanceType.*\]~s', $contentBeforeClass) !== 1) {
            return;
        }

        $message = 'It is forbidden to use Doctrine inheritance mapping because it causes problems during entity extension. Such problem with `OrderItem` was resolved during making OrderItem extendable #715.';

        $file->addError(
            $message,
            $classPosition,
            self::class,
        );
    }
}
