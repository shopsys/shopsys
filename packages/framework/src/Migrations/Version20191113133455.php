<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191113133455 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE administrator_roles (
                administrator_id INT NOT NULL,
                role VARCHAR(255) NOT NULL,
                PRIMARY KEY(administrator_id, role)
            )');
        $this->sql('CREATE INDEX IDX_680C3A6E4B09E92C ON administrator_roles (administrator_id)');
        $this->sql('
            ALTER TABLE
                administrator_roles
            ADD
                CONSTRAINT FK_680C3A6E4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('INSERT INTO administrator_roles(administrator_id, role) 
            SELECT id, 
                CASE WHEN superadmin = TRUE 
                    THEN \'ROLE_SUPER_ADMIN\' 
                    ELSE \'ROLE_ADMIN\' 
                END
            FROM administrators
        ');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
