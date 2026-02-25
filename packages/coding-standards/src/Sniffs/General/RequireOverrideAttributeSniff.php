<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs\General;

use Override;
use PHP_CodeSniffer\Files\File;
use ReflectionClass;
use SlevomatCodingStandard\Helpers\FunctionHelper;

class RequireOverrideAttributeSniff extends AbstractRequireOverrideAttributeSniff
{
    #[Override]
    protected function processClassMembers(
        File $phpcsFile,
        int $classPtr,
        ReflectionClass $parentClass,
    ): void {
        $tokens = $phpcsFile->getTokens();
        $classStartPtr = $tokens[$classPtr]['scope_opener'];
        $classEndPtr = $tokens[$classPtr]['scope_closer'];

        $methodPtr = $classStartPtr;

        while (($methodPtr = $phpcsFile->findNext(T_FUNCTION, $methodPtr + 1, $classEndPtr)) !== false) {
            if (!FunctionHelper::isMethod($phpcsFile, $methodPtr)) {
                continue;
            }

            $methodName = FunctionHelper::getName($phpcsFile, $methodPtr);

            if ($this->isMagicMethod($methodName)) {
                continue;
            }

            if (!$this->methodExistsInParentClass($parentClass, $methodName)) {
                continue;
            }

            if ($this->hasOverrideAttribute($phpcsFile, $methodPtr)) {
                continue;
            }

            $this->addOverrideAttributeError($phpcsFile, $methodPtr, $methodName, $parentClass->getShortName());
        }
    }

    /**
     * Checks magic methods like __construct, __destruct, __call, etc.
     */
    protected function isMagicMethod(string $methodName): bool
    {
        return str_starts_with($methodName, '__');
    }

    protected function methodExistsInParentClass(ReflectionClass $parentClass, string $methodName): bool
    {
        if ($parentClass->hasMethod($methodName)) {
            return true;
        }

        if ($parentClass->isInterface()) {
            foreach ($parentClass->getInterfaces() as $parentInterface) {
                if ($this->methodExistsInParentClass($parentInterface, $methodName)) {
                    return true;
                }
            }
        }

        // For classes, check parent class
        $parentOfParent = $parentClass->getParentClass();

        if ($parentOfParent !== false) {
            return $this->methodExistsInParentClass($parentOfParent, $methodName);
        }

        return false;
    }

    protected function hasOverrideAttribute(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $attributePtr = $phpcsFile->findPrevious(T_ATTRIBUTE, $stackPtr - 1);

        if ($attributePtr === false) {
            return false;
        }

        // Ensure the attribute belongs to this method, not a previous one
        $prevBoundary = $phpcsFile->findPrevious([T_FUNCTION, T_CLASS, T_TRAIT], $stackPtr - 1);

        if ($prevBoundary !== false && $attributePtr < $prevBoundary) {
            return false;
        }

        $attributeEnd = $phpcsFile->findNext(T_ATTRIBUTE_END, $attributePtr);

        if ($attributeEnd === false) {
            return false;
        }

        for ($i = $attributePtr; $i < $attributeEnd; $i++) {
            if ($tokens[$i]['code'] === T_STRING && $tokens[$i]['content'] === 'Override') {
                return true;
            }
        }

        return false;
    }

    protected function addOverrideAttributeError(
        File $phpcsFile,
        int $stackPtr,
        string $methodName,
        string $parentClassName,
    ): void {
        $error = "Method {$methodName} overrides {$parentClassName}::{$methodName} but is missing #[Override] attribute.";
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'MissingOverride');

        if (!$fix) {
            return;
        }

        $methodNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);
        $this->applyOverrideAttributeFix($phpcsFile, $methodNamePtr);
    }
}
