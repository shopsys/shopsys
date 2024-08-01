<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240801061521 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_users ADD sales_representative_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                customer_users
            ADD
                CONSTRAINT FK_DAB6D0D28B54B08B FOREIGN KEY (sales_representative_id) REFERENCES sales_representatives (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_DAB6D0D28B54B08B ON customer_users (sales_representative_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
