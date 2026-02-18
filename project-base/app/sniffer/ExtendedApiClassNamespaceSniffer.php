<?php

declare(strict_types=1);


namespace Sniffer;


use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ExtendedApiClassNamespaceSniffer implements Sniff
{
    private const FRONTEND_API_NAMESPACE_PART = 'FrontendApi';
    private const FRONTEND_API_BUNDLE_NAME = 'FrontendApiBundle';

    /**
     * @return array<int|string>
     */
    public function register(): array
    {
        return [
            T_EXTENDS
        ];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $numTokens = count($tokens);

        $parentClassNameFromExtend = $this->getParentClassNameFromExtend($phpcsFile, $stackPtr, $numTokens);

        if ($parentClassNameFromExtend === '') {
            return;
        }

        $classPosition = $phpcsFile->findNext([T_CLASS], 0);

        if ($classPosition === false) {
            return;
        }

        $foundUsePosition = false;
        for(
            $usePosition = $this->findNextUsePosition($phpcsFile, 0);
            $usePosition > 0 && $usePosition < $classPosition && $foundUsePosition === false;
            $usePosition = $this->findNextUsePosition($phpcsFile, $usePosition + 1)
        ) {
            $useClassName = $this->getUseClassName($phpcsFile, $usePosition, $numTokens);

            if($parentClassNameFromExtend === $useClassName){
                $foundUsePosition = $usePosition;
            }
        }

        if($foundUsePosition !== false){
            $this->checkNamespacesFromFrontendApi($phpcsFile, $foundUsePosition, $numTokens);
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $usePosition
     * @param int $numTokens
     */
    private function checkNamespacesFromFrontendApi(File $phpcsFile, int $usePosition, int $numTokens): void
    {
        $parentClassNamespaceParts = [];
        $this->getNamespacePartsArray($phpcsFile, $usePosition, $parentClassNamespaceParts, $numTokens);

        if (in_array(self::FRONTEND_API_BUNDLE_NAME, $parentClassNamespaceParts)) {

            $namespacePosition = $phpcsFile->findNext([T_NAMESPACE], 0);

            if ($namespacePosition === false) {
                return;
            }

            $currentClassNamespaceParts = [];
            $this->getNamespacePartsArray($phpcsFile, $namespacePosition, $currentClassNamespaceParts, $numTokens);

            if (in_array(self::FRONTEND_API_NAMESPACE_PART, $currentClassNamespaceParts)) {
                return;
            }

            if (in_array(self::FRONTEND_API_BUNDLE_NAME, $currentClassNamespaceParts)) {
                return;
            }

            $phpcsFile->addError('This file has wrong namespace, should be in "FrontendApi" namespace.', $namespacePosition, 'missing');
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @param array<string> $result
     * @param int $numTokens
     */
    private function getNamespacePartsArray(File $phpcsFile, int $stackPtr, array &$result, int $numTokens): void
    {
        if ($stackPtr >= $numTokens - 1) {
            return;
        }

        if ($this->isNextSpace($phpcsFile, $stackPtr)) {
            $stackPtr++;
        }

        if ($stackPtr >= $numTokens - 1) {
            return;
        }

        if ($this->isNextString($phpcsFile, $stackPtr)) {
            $stackPtr++;
            $result[] = $this->getContent($phpcsFile, $stackPtr);
            $this->getNamespacePartsArray($phpcsFile, $stackPtr, $result, $numTokens);
            return;
        }

        if ($this->isNextNsSeparator($phpcsFile, $stackPtr)) {
            $this->getNamespacePartsArray($phpcsFile, $stackPtr + 1, $result, $numTokens);
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @param int $numTokens
     * @return string
     */
    private function getUseClassName(File $phpcsFile, int $stackPtr, int $numTokens): string
    {
        if ($stackPtr >= $numTokens - 1) {
            return '';
        }

        if ($this->isNextSpace($phpcsFile, $stackPtr)) {
            $stackPtr++;
        }

        if ($stackPtr >= $numTokens - 1) {
            return '';
        }

        if ($this->isNextString($phpcsFile, $stackPtr)) {
            $stackPtr++;

            if ($this->isNextSemicolon($phpcsFile, $stackPtr)) {
                return $this->getContent($phpcsFile, $stackPtr);
            }
        }

        return $this->getUseClassName($phpcsFile, $stackPtr + 1, $numTokens);
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return int
     */
    private function findNextUsePosition(File $phpcsFile, int $stackPtr): int
    {
        $usePosition = $phpcsFile->findNext([T_USE], $stackPtr);
        if ($usePosition === false){
            return -1;
        }

        return $usePosition;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @param int $numTokens
     * @return string
     */
    private function getParentClassNameFromExtend(File $phpcsFile, int $stackPtr, int $numTokens): string
    {
        if ($stackPtr >= $numTokens - 1) {
            return '';
        }

        if ($this->isNextString($phpcsFile, $stackPtr)) {
            $stackPtr++;

            if ($this->isNextNsSeparator($phpcsFile, $stackPtr)) {
                $stackPtr++;
                return $this->getParentClassNameFromExtend($phpcsFile, $stackPtr, $numTokens);
            }

            return $this->getContent($phpcsFile, $stackPtr);
        }

        return $this->getParentClassNameFromExtend($phpcsFile, $stackPtr + 1, $numTokens);
    }


    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    private function isNextNsSeparator(File $phpcsFile, int $stackPtr): bool
    {
        $token = $phpcsFile->getTokens()[$stackPtr+1] ?? false;
        if ($token === false) {
            return false;
        }

        return $token['code'] === T_NS_SEPARATOR;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    private function isNextSemicolon(File $phpcsFile, int $stackPtr): bool
    {
        $token = $phpcsFile->getTokens()[$stackPtr+1] ?? false;
        if ($token === false) {
            return false;
        }

        return $token['code'] === T_SEMICOLON;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    private function isNextString(File $phpcsFile, int $stackPtr): bool
    {
        $token = $phpcsFile->getTokens()[$stackPtr+1] ?? false;
        if ($token === false) {
            return false;
        }

        return $token['code'] === T_STRING;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    private function isNextSpace(File $phpcsFile, int $stackPtr): bool
    {
        $token = $phpcsFile->getTokens()[$stackPtr+1] ?? false;
        if ($token === false) {
            return false;
        }

        return $token['code'] === T_WHITESPACE;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return string
     */
    private function getContent(File $phpcsFile, int $stackPtr): string
    {
        return $phpcsFile->getTokens()[$stackPtr]['content'];
    }

}
