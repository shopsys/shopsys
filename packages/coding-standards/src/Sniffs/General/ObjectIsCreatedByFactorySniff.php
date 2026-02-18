<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs\General;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Shopsys\CodingStandards\Helper\Naming;
use SlevomatCodingStandard\Helpers\ClassHelper;

final class ObjectIsCreatedByFactorySniff implements Sniff
{
    /**
     * @return array<int|string>
     */
    #[Override]
    public function register(): array
    {
        return [T_NEW];
    }

    /**
     * @param int $position
     */
    #[Override]
    public function process(File $file, $position): void
    {
        $tokens = $file->getTokens();

        $classNameTokenTypes = [T_STRING];

        if (defined('T_NAME_QUALIFIED')) {
            $classNameTokenTypes[] = T_NAME_QUALIFIED;
        }

        if (defined('T_NAME_FULLY_QUALIFIED')) {
            $classNameTokenTypes[] = T_NAME_FULLY_QUALIFIED;
        }

        $nextPosition = $file->findNext(T_WHITESPACE, $position + 1, null, true);

        if ($nextPosition === false) {
            return;
        }

        if (!in_array($tokens[$nextPosition]['code'], $classNameTokenTypes, true)) {
            return;
        }

        $naming = new Naming();

        $instantiatedClassName = $naming->getClassName($file, $nextPosition);
        $factoryClassName = $instantiatedClassName . 'Factory';
        $currentClassName = $this->getFirstClassNameInFile($file);

        if (!class_exists($factoryClassName) || is_a($currentClassName, $factoryClassName, true)) {
            return;
        }

        $file->addError(
            sprintf('For creation of "%s" class use its factory "%s"', $instantiatedClassName, $factoryClassName),
            $position,
            self::class,
        );
    }

    /**
     * We can not use Symplify\CodingStandard\TokenRunner\Analyzer\SnifferAnalyzer\Naming::getClassName()
     * as it does not include namespace of declared class.
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
