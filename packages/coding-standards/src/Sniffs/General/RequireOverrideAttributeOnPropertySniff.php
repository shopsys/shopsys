<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs\General;

use Override;
use PHP_CodeSniffer\Files\File;
use ReflectionClass;
use SlevomatCodingStandard\Helpers\PropertyHelper;

use const T_ANON_CLASS;

class RequireOverrideAttributeOnPropertySniff extends AbstractRequireOverrideAttributeSniff
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

        $variablePtr = $classStartPtr;

        while (($variablePtr = $phpcsFile->findNext(T_VARIABLE, $variablePtr + 1, $classEndPtr)) !== false) {
            if (!PropertyHelper::isProperty($phpcsFile, $variablePtr)) {
                continue;
            }

            if (!$this->isDirectClassProperty($tokens, $variablePtr, $classPtr)) {
                continue;
            }

            $propertyName = ltrim($tokens[$variablePtr]['content'], '$');

            if (!$this->propertyExistsInParentClass($parentClass, $propertyName)) {
                continue;
            }

            if ($this->hasOverrideAttribute($phpcsFile, $variablePtr)) {
                continue;
            }

            $this->addOverrideAttributeError($phpcsFile, $variablePtr, $propertyName, $parentClass->getShortName());
        }
    }

    /**
     * Checks that the property belongs directly to the class being processed,
     * not to a nested anonymous class.
     *
     * @param array<int, array<string, mixed>> $tokens
     */
    protected function isDirectClassProperty(array $tokens, int $variablePtr, int $classPtr): bool
    {
        $conditions = $tokens[$variablePtr]['conditions'];

        foreach (array_reverse($conditions, true) as $condPtr => $condType) {
            if (in_array($condType, [T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
                return $condPtr === $classPtr;
            }
        }

        return false;
    }

    protected function propertyExistsInParentClass(ReflectionClass $parentClass, string $propertyName): bool
    {
        if ($parentClass->hasProperty($propertyName)) {
            $property = $parentClass->getProperty($propertyName);

            return !$property->isPrivate();
        }

        if ($parentClass->isInterface()) {
            foreach ($parentClass->getInterfaces() as $parentInterface) {
                if ($this->propertyExistsInParentClass($parentInterface, $propertyName)) {
                    return true;
                }
            }
        }

        $parentOfParent = $parentClass->getParentClass();

        if ($parentOfParent !== false) {
            return $this->propertyExistsInParentClass($parentOfParent, $propertyName);
        }

        return false;
    }

    protected function hasOverrideAttribute(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        $prevBoundary = $phpcsFile->findPrevious(
            [T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET],
            $stackPtr - 1,
        );

        $searchStart = ($prevBoundary !== false) ? $prevBoundary + 1 : 0;

        $attributePtr = $searchStart;

        while (($attributePtr = $phpcsFile->findNext(T_ATTRIBUTE, $attributePtr, $stackPtr)) !== false) {
            $attributeEnd = $phpcsFile->findNext(T_ATTRIBUTE_END, $attributePtr);

            if ($attributeEnd === false) {
                $attributePtr++;

                continue;
            }

            for ($i = $attributePtr; $i <= $attributeEnd; $i++) {
                if ($tokens[$i]['code'] === T_STRING && $tokens[$i]['content'] === 'Override') {
                    return true;
                }
            }

            $attributePtr = $attributeEnd + 1;
        }

        return false;
    }

    protected function addOverrideAttributeError(
        File $phpcsFile,
        int $stackPtr,
        string $propertyName,
        string $parentClassName,
    ): void {
        $error = "Property \${$propertyName} overrides {$parentClassName}::\${$propertyName} but is missing #[Override] attribute.";
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'MissingOverride');

        if (!$fix) {
            return;
        }

        $this->applyOverrideAttributeFix($phpcsFile, $stackPtr);
    }
}
