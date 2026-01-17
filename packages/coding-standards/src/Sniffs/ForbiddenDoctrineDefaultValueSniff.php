<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use const T_CLASS;
use const T_DOC_COMMENT_OPEN_TAG;

class ForbiddenDoctrineDefaultValueSniff implements Sniff
{
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
        $docBlockOpeningTagPositions = $this->getAllDocBlockOpeningTagPositions($file, $classPosition);

        foreach ($docBlockOpeningTagPositions as $docBlockOpenTagPosition) {
            $docBlockToken = $tokens[$docBlockOpenTagPosition];

            $content = TokenHelper::getContent($file, $docBlockOpenTagPosition, $docBlockToken['comment_closer']);

            if ($this->annotationContainsDefaultValue($content)) {
                $file->addError(
                    'Default value of entity properties cannot be used.',
                    $docBlockOpenTagPosition,
                    self::class,
                );
            }
        }
    }

    protected function annotationContainsDefaultValue(string $annotationString): bool
    {
        return (bool)preg_match('~options\s*=\s*\{\s*.*"default"~', $annotationString);
    }

    /**
     * @return int[]
     */
    protected function getAllDocBlockOpeningTagPositions(File $file, int $startPosition): array
    {
        $tokens = $file->getTokens();
        $classToken = $tokens[$startPosition];

        return TokenHelper::findNextAll(
            $file,
            [T_DOC_COMMENT_OPEN_TAG],
            $classToken['scope_opener'],
            $classToken['scope_closer'],
        );
    }
}
