<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250117110742 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            ALTER TABLE
                ready_category_seo_mixes RENAME COLUMN chose_category_seo_mix_combination_json TO selected_category_seo_mix_combination_json');
        $this->sql('
            CREATE UNIQUE INDEX selected_category_seo_mix_combination_json ON ready_category_seo_mixes (
                selected_category_seo_mix_combination_json
            )');
        $this->sql('DROP INDEX chose_category_seo_mix_combination_json');
    }
}
