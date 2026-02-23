<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260223133152 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE heureka_category ADD locale VARCHAR(255) NOT NULL DEFAULT \'cs\'');
        $this->sql('ALTER TABLE heureka_category ALTER locale DROP DEFAULT');

        $this->sql('ALTER TABLE heureka_category ADD heureka_id INT NOT NULL DEFAULT 0');
        $this->sql('ALTER TABLE heureka_category ALTER heureka_id DROP DEFAULT');
        $this->sql('UPDATE heureka_category SET heureka_id = id');

        $this->sql('CREATE SEQUENCE heureka_category_id_seq');
        $this->sql('SELECT setval(\'heureka_category_id_seq\', (SELECT MAX(id) FROM heureka_category))');
        $this->sql('ALTER TABLE heureka_category ALTER id SET DEFAULT nextval(\'heureka_category_id_seq\')');

        $this->sql('DROP INDEX uniq_fe112a6912469de2;');

        $this->sql('CREATE UNIQUE INDEX uq_heureka_category_heureka_id_locale ON heureka_category (locale, heureka_id)');
        $this->sql('CREATE INDEX IDX_FE112A6912469DE2 ON heureka_category_categories (category_id)');
    }
}
