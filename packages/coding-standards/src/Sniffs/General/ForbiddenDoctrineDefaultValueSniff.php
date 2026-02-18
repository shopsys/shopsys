<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs\General;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use const T_ATTRIBUTE;
use const T_CLASS;

class ForbiddenDoctrineDefaultValueSniff implements Sniff
{
    #[Override]
    public function register(): array
    {
        return [T_CLASS];
    }

    /**
     * @param int $classPosition
     */
    #[Override]
    public function process(File $file, $classPosition): void
    {
        $tokens = $file->getTokens();
        $classToken = $tokens[$classPosition];

        $attributePositions = TokenHelper::findNextAll(
            $file,
            [T_ATTRIBUTE],
            $classToken['scope_opener'],
            $classToken['scope_closer'],
        );

        foreach ($attributePositions as $attributePosition) {
            $attributeToken = $tokens[$attributePosition];

            if (!isset($attributeToken['attribute_closer'])) {
                continue;
            }

            $content = TokenHelper::getContent($file, $attributePosition, $attributeToken['attribute_closer']);

            if ($this->attributeContainsDefaultValue($content)) {
                $file->addError(
                    'Default value of entity properties cannot be used.',
                    $attributePosition,
                    self::class,
                );
            }
        }
    }

    protected function attributeContainsDefaultValue(string $attributeString): bool
    {
        return (bool)preg_match('~options\s*:\s*\[.*[\'"]default[\'"]\s*=>~s', $attributeString);
    }
}
