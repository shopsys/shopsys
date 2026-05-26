<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260415120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE uploaded_files ADD filesize INT DEFAULT NULL');
        $this->sql('ALTER TABLE customer_uploaded_files ADD filesize INT DEFAULT NULL');
        $this->sql('ALTER TABLE images ADD filesize INT DEFAULT NULL');
    }
}
