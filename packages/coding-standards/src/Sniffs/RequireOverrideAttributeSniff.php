<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ReflectionClass;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

use const T_ANON_CLASS;

class RequireOverrideAttributeSniff implements Sniff
{
    /**
     * @return array<int>
     */
    #[Override]
    public function register(): array
    {
        return [T_CLASS];
    }

    /**
     * @param mixed $stackPtr
     * @throws \ReflectionException
     */
    #[Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_ANON_CLASS) {
            return;
        }

        $classNamesToCheck = $this->getInterfaceNames($phpcsFile, $stackPtr);
        $parentClassName = $this->getParentClassName($phpcsFile, $stackPtr);

        if ($parentClassName !== null) {
            $classNamesToCheck[] = $parentClassName;
        }

        foreach ($classNamesToCheck as $className) {
            $this->processClassMethods($phpcsFile, $stackPtr, new ReflectionClass($className));
        }
    }

    private function processClassMethods(
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

    private function getParentClassName(File $phpcsFile, int $stackPtr): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $extendsPtr = $phpcsFile->findNext(T_EXTENDS, $stackPtr, $tokens[$stackPtr]['scope_opener']);

        if ($extendsPtr === false) {
            return null;
        }

        $parentClassPtr = $phpcsFile->findNext(T_STRING, $extendsPtr);

        if ($parentClassPtr === false) {
            return null;
        }

        $parentClassName = $tokens[$parentClassPtr]['content'];

        return $this->resolveClassName($phpcsFile, $parentClassName, $parentClassPtr);
    }

    /**
     * @return array<string>
     */
    private function getInterfaceNames(File $phpcsFile, int $stackPtr): array
    {
        $tokens = $phpcsFile->getTokens();
        $interfaceNames = [];

        $implementsPtr = $phpcsFile->findNext(T_IMPLEMENTS, $stackPtr, $tokens[$stackPtr]['scope_opener']);

        if ($implementsPtr === false) {
            return $interfaceNames;
        }

        $currentPtr = $implementsPtr;
        $classOpenPtr = $tokens[$stackPtr]['scope_opener'];

        while (($currentPtr = $phpcsFile->findNext(T_STRING, $currentPtr + 1, $classOpenPtr)) !== false) {
            $interfaceName = $tokens[$currentPtr]['content'];
            $resolvedName = $this->resolveClassName($phpcsFile, $interfaceName, $currentPtr);

            if ($resolvedName !== null) {
                $interfaceNames[] = $resolvedName;
            }
        }

        return $interfaceNames;
    }

    private function resolveClassName(File $phpcsFile, string $className, int $position): ?string
    {
        $allUseStatements = UseStatementHelper::getFileUseStatements($phpcsFile);

        foreach ($allUseStatements as $useStatements) {
            // PHP use statements are case-insensitive
            $lowerClassName = strtolower($className);

            if (isset($useStatements[$lowerClassName])) {
                return $useStatements[$lowerClassName]->getFullyQualifiedTypeName();
            }
        }

        $namespace = NamespaceHelper::findCurrentNamespaceName($phpcsFile, $position);

        return $namespace ? $namespace . '\\' . $className : $className;
    }

    /**
     * Checks magic methods like __construct, __destruct, __call, etc.
     */
    private function isMagicMethod(string $methodName): bool
    {
        return str_starts_with($methodName, '__');
    }

    private function methodExistsInParentClass(ReflectionClass $parentClass, string $methodName): bool
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

    private function hasOverrideAttribute(File $phpcsFile, int $stackPtr): bool
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

    private function hasOverrideUseStatement(File $phpcsFile): bool
    {
        $allUseStatements = UseStatementHelper::getFileUseStatements($phpcsFile);

        foreach ($allUseStatements as $useStatements) {
            // Use statements are stored with lowercase keys
            if (isset($useStatements['override'])) {
                return true;
            }
        }

        return false;
    }

    private function addUseStatement(File $phpcsFile, string $className): void
    {
        // First try to find existing use statements
        $usePtr = $phpcsFile->findNext(T_USE, 0);

        if ($usePtr !== false) {
            // There are existing use statements, add before the first one
            $previousLinePtr = $phpcsFile->findPrevious(T_WHITESPACE, $usePtr, null, false, PHP_EOL);
            $phpcsFile->fixer->addContentBefore($previousLinePtr + 1, 'use ' . $className . ';' . PHP_EOL);

            return;
        }

        // No existing use statements, find the namespace and add after it
        $namespacePtr = $phpcsFile->findNext(T_NAMESPACE, 0);

        if ($namespacePtr === false) {
            return; // No namespace found, can't add use statement
        }

        // Find the semicolon after the namespace declaration
        $semicolonPtr = $phpcsFile->findNext(T_SEMICOLON, $namespacePtr);

        if ($semicolonPtr === false) {
            return;
        }

        // Add the use statement after the namespace
        $phpcsFile->fixer->addContentBefore($semicolonPtr + 1, PHP_EOL . PHP_EOL . 'use ' . $className . ';');
    }
}
