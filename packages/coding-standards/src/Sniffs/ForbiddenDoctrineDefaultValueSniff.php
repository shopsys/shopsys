<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use const T_CLASS;
use const T_DOC_COMMENT_OPEN_TAG;

class ForbiddenDoctrineDefaultValueSniff implements Sniff
{
    /**
     * @return array
     */
    public function register(): array
    {
        return [T_CLASS];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $classPosition
     */
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
                    self::class
                );
            }
        }
    }

    /**
     * @param string $annotationString
     * @return bool
     */
    protected function annotationContainsDefaultValue(string $annotationString): bool
    {
        return (bool)preg_match('~options\s*=\s*\{\s*.*"default"~', $annotationString);
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $startPosition
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
            $classToken['scope_closer']
        );
    }
}
