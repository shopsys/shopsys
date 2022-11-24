<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Shopsys\CodingStandards\Helper\Naming;
use SlevomatCodingStandard\Helpers\ClassHelper;

final class ObjectIsCreatedByFactorySniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_NEW];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @param int $position
     */
    public function process(File $file, int $position): void
    {
        $endPosition = $file->findEndOfStatement($position);
        $instantiatedClassNamePosition = $file->findNext(T_STRING, $position, $endPosition);

        if ($instantiatedClassNamePosition === false) {
            // eg. new $className; cannot be resolved
            return;
        }

        $naming = new Naming();

        $instantiatedClassName = $naming->getClassName($file, $instantiatedClassNamePosition);
        $factoryClassName = $instantiatedClassName . 'Factory';
        $currentClassName = $this->getFirstClassNameInFile($file);

        if (!class_exists($factoryClassName) || is_a($currentClassName, $factoryClassName, true)) {
            return;
        }

        $file->addError(
            sprintf('For creation of "%s" class use its factory "%s"', $instantiatedClassName, $factoryClassName),
            $position,
            self::class
        );
    }

    /**
     * We can not use Symplify\CodingStandard\TokenRunner\Analyzer\SnifferAnalyzer\Naming::getClassName()
     * as it does not include namespace of declared class.
     *
     * @param \PHP_CodeSniffer\Files\File $file
     * @return string|null
     */
    private function getFirstClassNameInFile(File $file): ?string
    {
        $position = $file->findNext(T_CLASS, 0);

        if ($position === false) {
            return null;
        }

        $fileClassName = ClassHelper::getFullyQualifiedName($file, $position);

        return ltrim($fileClassName, '\\');
    }
}
