<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220222103841 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE administrators ADD role_group_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                administrators
            ADD
                CONSTRAINT FK_73A716FD4873F76 FOREIGN KEY (role_group_id) REFERENCES administrator_role_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_73A716FD4873F76 ON administrators (role_group_id)');
        $this->sql('CREATE UNIQUE INDEX UNIQ_2D0D81B55E237E06 ON administrator_role_groups (name)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
