<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ReflectionClass;
use ReflectionException;

class RequireOverrideAttributeSniff implements Sniff
{
    /**
     * @return array
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param mixed $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $classPtr = $phpcsFile->findPrevious([T_CLASS, T_TRAIT], $stackPtr);

        if ($classPtr === false) {
            return;
        }

        $classNamePtr = $phpcsFile->findNext(T_STRING, $classPtr);

        if ($classNamePtr === false) {
            return;
        }
        $className = $tokens[$classNamePtr]['content'];

        $methodNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);

        if ($methodNamePtr === false) {
            return;
        }
        $methodName = $tokens[$methodNamePtr]['content'];

        if ($methodName === '__construct') {
            return;
        }

        $parentClassPtr = $phpcsFile->findNext(T_EXTENDS, $classNamePtr);

        if ($parentClassPtr === false) {
            return;
        }

        $parentClassNamePtr = $phpcsFile->findNext(T_STRING, $parentClassPtr);

        if ($parentClassNamePtr === false) {
            return;
        }

        $parentClassName = $tokens[$parentClassNamePtr]['content'];
        $parentClassFqn = $this->getFqnOfClass($phpcsFile, $tokens, $parentClassName);

        if ($parentClassFqn === '') {
            return;
        }


        try {
            $parentClassReflection = new ReflectionClass($parentClassFqn);

            $hasMethod = $this->hasParentClassMethod($parentClassReflection, $methodName);
        } catch (ReflectionException $e) {
            return;
        }

        if ($hasMethod === false) {
            return;
        }

        $hasOverride = $this->checkMethodHasOverrideAttribute($phpcsFile, $methodNamePtr, $tokens, $hasOverride);

        if ($hasOverride === true) {
            return;
        }

        $error = "Method {$className}::{$methodName} overrides {$parentClassName}::{$methodName} but is missing #[Override] attribute.";
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'MissingOverride');

        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();

        $previousLine = $phpcsFile->findPrevious(T_WHITESPACE, $methodNamePtr, value: PHP_EOL);
        $indent = $tokens[$previousLine + 1]['content'];
        $phpcsFile->fixer->addContentBefore($previousLine + 1, "{$indent}#[Override]" . PHP_EOL);

        $overrideFqn = $this->getFqnOfClass($phpcsFile, $tokens, 'Override');

        if ($overrideFqn === '') {
            $usePtr = $phpcsFile->findNext(T_USE, 0);
            $previousLine = $phpcsFile->findPrevious(T_WHITESPACE, $usePtr, value: PHP_EOL);
            $phpcsFile->fixer->addContentBefore($previousLine + 1, 'use Override;' . PHP_EOL);
        }

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @param \ReflectionClass $parentClass
     * @param string $methodName
     * @return bool
     */
    protected function hasParentClassMethod(ReflectionClass $parentClass, string $methodName): bool
    {
        if ($parentClass->hasMethod($methodName)) {
            return true;
        }

        $parentClass = $parentClass->getParentClass();

        if ($parentClass === false) {
            return false;
        }

        return $parentClass->hasMethod($methodName);
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param array $tokens
     * @param string $parentClassName
     * @return string
     */
    private function getFqnOfClass(File $phpcsFile, array $tokens, mixed $parentClassName): string
    {
        $usePtr = $phpcsFile->findNext(T_USE, 0);

        $parentClassFqn = '';

        while ($usePtr !== false) {
            $semicolonPtr = $phpcsFile->findNext(T_SEMICOLON, $usePtr);
            $fqnClassNamePtr = $phpcsFile->findPrevious(T_STRING, $semicolonPtr);

            $fqnClassName = $tokens[$fqnClassNamePtr]['content'];

            if ($fqnClassName !== $parentClassName) {
                $usePtr = $phpcsFile->findNext(T_USE, $usePtr + 1);

                continue;
            }

            $fqnCurrentPtr = $usePtr + 2;

            while ($tokens[$fqnCurrentPtr]['type'] !== 'T_WHITESPACE' && $tokens[$fqnCurrentPtr]['type'] !== 'T_SEMICOLON') {
                $parentClassFqn .= $tokens[$fqnCurrentPtr]['content'];
                $fqnCurrentPtr++;
            }

            break;
        }

        return $parentClassFqn;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $methodNamePtr
     * @param array $tokens
     * @return bool
     */
    private function checkMethodHasOverrideAttribute(File $phpcsFile, int $methodNamePtr, array $tokens): bool
    {
        $hasOverride = false;
        $previousLinePtr = $phpcsFile->findPrevious(T_WHITESPACE, $methodNamePtr, value: PHP_EOL);

        for ($i = $previousLinePtr - 1; $i >= 0; $i--) {
            if ($tokens[$i]['code'] === T_ATTRIBUTE) {
                $attributeNamePtr = $phpcsFile->findNext(T_STRING, $i);

                if ($attributeNamePtr !== false && $tokens[$attributeNamePtr]['content'] === 'Override') {
                    $hasOverride = true;

                    break;
                }
            }

            if ($tokens[$i]['code'] === T_DOC_COMMENT_CLOSE_TAG || $tokens[$i]['code'] === T_FUNCTION) {
                break;
            }
        }

        return $hasOverride;
    }
}
