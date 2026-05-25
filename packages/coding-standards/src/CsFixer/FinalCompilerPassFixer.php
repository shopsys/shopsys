<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;

class FinalCompilerPassFixer extends AbstractFinalClassByParentFixer
{
    #[Override]
    protected function getDescription(): string
    {
        return 'Compiler passes implementing CompilerPassInterface must be final.';
    }

    #[Override]
    protected function getMatchingParentClasses(): array
    {
        return [
            'CompilerPassInterface',
            'Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface',
            '\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface',
        ];
    }

    #[Override]
    public function getName(): string
    {
        return 'Shopsys/final_compiler_pass';
    }
}
