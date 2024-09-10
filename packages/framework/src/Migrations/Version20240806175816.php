<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240806175816 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_uploaded_files ADD customer_user_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE customer_uploaded_files ADD hash VARCHAR(32) DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                customer_uploaded_files
            ADD
                CONSTRAINT FK_970DDB7FBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_970DDB7FBBB3772B ON customer_uploaded_files (customer_user_id)');
        $this->sql('
            CREATE INDEX IDX_970DDB7FBF396750989D9B629FB73D77BBB3772B ON customer_uploaded_files (
                id, slug, extension, customer_user_id
            )');
        $this->sql('
            CREATE INDEX IDX_970DDB7FBF396750989D9B629FB73D77D1B862B8 ON customer_uploaded_files (id, slug, extension, hash)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
