<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ReflectionClass;
use ReflectionException;
use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

use const T_ANON_CLASS;

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

        // Find the class containing this method
        $classPtr = ClassHelper::getClassPointer($phpcsFile, $stackPtr);

        if ($classPtr === null) {
            return;
        }

        // Skip anonymous classes
        if ($tokens[$classPtr]['code'] === T_ANON_CLASS) {
            return;
        }

        // Get method name
        $methodName = FunctionHelper::getName($phpcsFile, $stackPtr);

        if ($methodName === null) {
            return;
        }

        // Skip constructors
        if ($methodName === '__construct') {
            return;
        }

        // Get parent class name from extends clause
        $parentClassName = $this->getParentClassName($phpcsFile, $classPtr);

        if ($parentClassName === null) {
            return;
        }

        // Resolve parent class FQN
        $parentClassFqn = $this->resolveClassName($phpcsFile, $parentClassName);

        if ($parentClassFqn === null) {
            return;
        }

        // Check if method exists in parent class
        if (!$this->methodExistsInParentClass($parentClassFqn, $methodName)) {
            return;
        }

        // Check if method already has #[Override] attribute
        if ($this->hasOverrideAttribute($phpcsFile, $stackPtr)) {
            return;
        }

        // Add error and fix
        $this->addOverrideAttributeError($phpcsFile, $stackPtr, $methodName, $parentClassName);
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $classPtr
     * @return string|null
     */
    private function getParentClassName(File $phpcsFile, int $classPtr): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $classBracePtr = $tokens[$classPtr]['scope_opener'];

        // Look for 'extends' keyword after class name
        $extendsPtr = $phpcsFile->findNext(T_EXTENDS, $classPtr, $classBracePtr);

        if ($extendsPtr === false) {
            return null;
        }

        // Get parent class name after 'extends'
        $parentClassNamePtr = $phpcsFile->findNext(T_STRING, $extendsPtr, $classBracePtr);

        if ($parentClassNamePtr === false) {
            return null;
        }

        return $tokens[$parentClassNamePtr]['content'];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param string $className
     * @return string|null
     */
    private function resolveClassName(File $phpcsFile, string $className): ?string
    {
        // Get all use statements for the file
        $allUseStatements = UseStatementHelper::getFileUseStatements($phpcsFile);

        // Check all use statement groups
        foreach ($allUseStatements as $useStatements) {
            if (isset($useStatements[$className])) {
                return $useStatements[$className]->getFullyQualifiedTypeName();
            }
        }

        // If no use statement found, try to resolve with current namespace
        $namespace = NamespaceHelper::findCurrentNamespaceName($phpcsFile, 0);

        return $namespace ? $namespace . '\\' . $className : $className;
    }

    /**
     * @param string $parentClassFqn
     * @param string $methodName
     * @return bool
     */
    private function methodExistsInParentClass(string $parentClassFqn, string $methodName): bool
    {
        try {
            $parentClass = new ReflectionClass($parentClassFqn);

            // Check if method exists in parent class hierarchy (not interfaces)
            while ($parentClass !== false) {
                if ($parentClass->hasMethod($methodName)) {
                    $method = $parentClass->getMethod($methodName);
                    $declaringClass = $method->getDeclaringClass();

                    // Only consider methods from classes, not interfaces
                    if (!$declaringClass->isInterface()) {
                        return true;
                    }
                }
                $parentClass = $parentClass->getParentClass();
            }
        } catch (ReflectionException) {
            return false;
        }

        return false;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    private function hasOverrideAttribute(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        // Look backwards for #[Override] attribute before this method
        for ($i = $stackPtr - 1; $i >= 0; $i--) {
            if ($tokens[$i]['code'] === T_ATTRIBUTE) {
                // Find the attribute content between [ and ]
                $attributeEnd = $phpcsFile->findNext(T_ATTRIBUTE_END, $i);

                if ($attributeEnd !== false) {
                    // Check if 'Override' is anywhere between the attribute brackets
                    for ($j = $i; $j < $attributeEnd; $j++) {
                        if ($tokens[$j]['code'] === T_STRING && $tokens[$j]['content'] === 'Override') {
                            return true;
                        }
                    }
                }
            }

            // Stop if we hit another method or class
            if (in_array($tokens[$i]['code'], [T_FUNCTION, T_CLASS, T_TRAIT], true)) {
                break;
            }
        }

        return false;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @param string $methodName
     * @param string $parentClassName
     */
    private function addOverrideAttributeError(
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

        $tokens = $phpcsFile->getTokens();
        $phpcsFile->fixer->beginChangeset();

        // Find the line before the method
        $methodNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);
        $previousLinePtr = $phpcsFile->findPrevious(T_WHITESPACE, $methodNamePtr, null, false, PHP_EOL);

        // Get indentation from the next line
        $indent = '';

        if ($previousLinePtr !== false && isset($tokens[$previousLinePtr + 1])) {
            $nextToken = $tokens[$previousLinePtr + 1];

            if ($nextToken['code'] === T_WHITESPACE) {
                $indent = $nextToken['content'];
            }
        }

        // Add the #[Override] attribute
        $phpcsFile->fixer->addContentBefore($previousLinePtr + 1, $indent . '#[Override]' . PHP_EOL);

        // Add use statement if needed
        if (!$this->hasOverrideUseStatement($phpcsFile)) {
            $this->addUseStatement($phpcsFile, 'Override');
        }

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @return bool
     */
    private function hasOverrideUseStatement(File $phpcsFile): bool
    {
        $allUseStatements = UseStatementHelper::getFileUseStatements($phpcsFile);

        foreach ($allUseStatements as $useStatements) {
            if (isset($useStatements['Override'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param string $className
     */
    private function addUseStatement(File $phpcsFile, string $className): void
    {
        $usePtr = $phpcsFile->findNext(T_USE, 0);

        if ($usePtr === false) {
            return;
        }

        $previousLinePtr = $phpcsFile->findPrevious(T_WHITESPACE, $usePtr, null, false, PHP_EOL);
        $phpcsFile->fixer->addContentBefore($previousLinePtr + 1, 'use ' . $className . ';' . PHP_EOL);
    }
}
