<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs\General;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ReflectionClass;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\UseStatementHelper;

use const T_ANON_CLASS;

abstract class AbstractRequireOverrideAttributeSniff implements Sniff
{
    /**
     * @return array<int|string>
     */
    #[Override]
    public function register(): array
    {
        return [T_CLASS];
    }

    /**
     * @param int $stackPtr
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
            $this->processClassMembers($phpcsFile, $stackPtr, new ReflectionClass($className));
        }
    }

    abstract protected function processClassMembers(
        File $phpcsFile,
        int $classPtr,
        ReflectionClass $parentClass,
    ): void;

    protected function getParentClassName(File $phpcsFile, int $stackPtr): ?string
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
    protected function getInterfaceNames(File $phpcsFile, int $stackPtr): array
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

    protected function resolveClassName(File $phpcsFile, string $className, int $position): ?string
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
     * Adds the #[Override] attribute before the given token pointer and a `use Override;` statement if needed.
     */
    protected function applyOverrideAttributeFix(File $phpcsFile, int $searchFromPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $phpcsFile->fixer->beginChangeset();

        $previousLinePtr = $phpcsFile->findPrevious(T_WHITESPACE, $searchFromPtr, null, false, PHP_EOL);

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

    protected function hasOverrideUseStatement(File $phpcsFile): bool
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

    protected function addUseStatement(File $phpcsFile, string $className): void
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
