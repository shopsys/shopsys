<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Helper;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\ReferencedNameHelper;

class Naming
{
    /**
     * @var string
     */
    private const NAMESPACE_SEPARATOR = '\\';

    /**
     * @var mixed[][]
     */
    private array $referencedNamesByFilePath = [];

    /**
     * @var string[][]
     */
    private array $fqnClassNameByFilePathAndClassName = [];

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $classNameStartPosition
     * @return string
     */
    public function getClassName(File $file, int $classNameStartPosition): string
    {
        $tokens = $file->getTokens();

        $firstNamePart = $tokens[$classNameStartPosition]['content'];

        // is class <name>
        if ($this->isClassName($file, $classNameStartPosition)) {
            $namespace = NamespaceHelper::findCurrentNamespaceName($file, $classNameStartPosition);
            if ($namespace) {
                return $namespace . self::NAMESPACE_SEPARATOR . $firstNamePart;
            }

            return $firstNamePart;
        }

        $classNameParts = [];
        $classNameParts[] = $firstNamePart;

        $nextTokenPointer = $classNameStartPosition + 1;
        while ($tokens[$nextTokenPointer]['code'] === T_NS_SEPARATOR) {
            ++$nextTokenPointer;
            $classNameParts[] = $tokens[$nextTokenPointer]['content'];
            ++$nextTokenPointer;
        }

        $completeClassName = implode(self::NAMESPACE_SEPARATOR, $classNameParts);

        $fqnClassName = $this->getFqnClassName($file, $completeClassName, $classNameStartPosition);
        if ($fqnClassName !== '') {
            return ltrim($fqnClassName, self::NAMESPACE_SEPARATOR);
        }

        return $completeClassName;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param string $className
     * @param int $classTokenPosition
     * @return string
     */
    private function getFqnClassName(File $file, string $className, int $classTokenPosition): string
    {
        $referencedNames = $this->getReferencedNames($file);

        foreach ($referencedNames as $referencedName) {
            if (isset($this->fqnClassNameByFilePathAndClassName[$file->path][$className])) {
                return $this->fqnClassNameByFilePathAndClassName[$file->path][$className];
            }

            $resolvedName = NamespaceHelper::resolveClassName(
                $file,
                $referencedName->getNameAsReferencedInFile(),
                $classTokenPosition,
            );

            if ($referencedName->getNameAsReferencedInFile() === $className) {
                $this->fqnClassNameByFilePathAndClassName[$file->path][$className] = $resolvedName;

                return $resolvedName;
            }
        }

        return '';
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     * @return bool
     */
    private function isClassName(File $file, int $position): bool
    {
        return (bool)$file->findPrevious(T_CLASS, $position, max(1, $position - 3));
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @return array
     */
    private function getReferencedNames(File $file): array
    {
        if (isset($this->referencedNamesByFilePath[$file->path])) {
            return $this->referencedNamesByFilePath[$file->path];
        }

        $referencedNames = ReferencedNameHelper::getAllReferencedNames($file, 0);

        $this->referencedNamesByFilePath[$file->path] = $referencedNames;

        return $referencedNames;
    }
}
