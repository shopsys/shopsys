<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;

class FinalMigrationFixer extends AbstractFinalClassByParentFixer
{
    #[Override]
    protected function getDescription(): string
    {
        return 'Doctrine migrations extending AbstractMigration must be final.';
    }

    #[Override]
    protected function getMatchingParentClasses(): array
    {
        return [
            'Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration',
            'Doctrine\Migrations\AbstractMigration',
        ];
    }

    #[Override]
    public function getName(): string
    {
        return 'Shopsys/final_migration';
    }
}
