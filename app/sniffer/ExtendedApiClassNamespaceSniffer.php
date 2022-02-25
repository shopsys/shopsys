<?php

declare(strict_types=1);


namespace Sniffer;


use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ExtendedApiClassNamespaceSniffer implements Sniff
{
    private const FRONTEND_API_NAMESPACE_PART = 'FrontendApi';
    private const FRONTEND_API_BUNDLE_NAME = 'FrontendApiBundle';

    public function register()
    {
        return [
            T_EXTENDS
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $parentClassNameFromExtend = $this->getParentClassNameFromExtend($phpcsFile, $stackPtr);

        $classPosition = $phpcsFile->findNext([T_CLASS], 0);
        $foundUsePosition = false;
        for(
            $usePosition = $this->findNextUsePosition($phpcsFile, 0);
            $usePosition > 0 && $usePosition < $classPosition && $foundUsePosition === false;
            $usePosition = $this->findNextUsePosition($phpcsFile, ++$usePosition)
        ) {

            $useClassName = $this->getUseClassName($phpcsFile, $usePosition);
            if($parentClassNameFromExtend === $useClassName){
                $foundUsePosition = $usePosition;
            }
        }

        if($foundUsePosition !== false){
            $this->checkNamespacesFromFrontendApi($phpcsFile, $foundUsePosition);
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $usePosition
     */
    private function checkNamespacesFromFrontendApi(File $phpcsFile, int $usePosition): void
    {
        $parentClassNamespaceParts = [];
        $this->getNamespacePartsArray($phpcsFile, $usePosition, $parentClassNamespaceParts);

        if (in_array(self::FRONTEND_API_BUNDLE_NAME, $parentClassNamespaceParts)) {

            $namespacePosition = $phpcsFile->findNext([T_NAMESPACE], 0);
            $currentClassNamespaceParts = [];
            $this->getNamespacePartsArray($phpcsFile, $namespacePosition, $currentClassNamespaceParts);

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
     * @param array $result
     */
    private function getNamespacePartsArray(File $phpcsFile, int $stackPtr, array &$result): void
    {
        if ($this->isNextSpace($phpcsFile, $stackPtr)) {
            $stackPtr++;
        }

        if ($this->isNextString($phpcsFile, $stackPtr)) {
            $stackPtr++;
            $result[] = $this->getContent($phpcsFile, $stackPtr);
            $this->getNamespacePartsArray($phpcsFile, $stackPtr, $result);
            return;
        }

        if ($this->isNextNsSeparator($phpcsFile, $stackPtr)) {
            $this->getNamespacePartsArray($phpcsFile, ++$stackPtr, $result);
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @return string
     */
    private function getUseClassName(File $phpcsFile, int $stackPtr): string
    {
        if ($this->isNextSpace($phpcsFile, $stackPtr)) {
            $stackPtr++;
        }

        if ($this->isNextString($phpcsFile, $stackPtr)) {
            $stackPtr++;

            if ($this->isNextSemicolon($phpcsFile, $stackPtr)) {
                return $this->getContent($phpcsFile, $stackPtr);
            }
        }

        return $this->getUseClassName($phpcsFile, ++$stackPtr);
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
     * @return string
     */
    private function getParentClassNameFromExtend(File $phpcsFile, int $stackPtr): string
    {
        if ($this->isNextString($phpcsFile, $stackPtr)) {
            $stackPtr++;

            if ($this->isNextNsSeparator($phpcsFile, $stackPtr)) {
                $stackPtr++;
                return $this->getParentClassNameFromExtend($phpcsFile, $stackPtr);
            }

            return $this->getContent($phpcsFile, $stackPtr);
        }

        return $this->getParentClassNameFromExtend($phpcsFile, ++$stackPtr);
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