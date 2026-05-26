<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241114072907 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE slider_items DROP COLUMN slider_extended_text');
        $this->sql('ALTER TABLE slider_items DROP COLUMN slider_extended_text_link');
    }
}
