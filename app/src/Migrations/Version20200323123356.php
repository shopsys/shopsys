<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200323123356 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_domain_flags (
                product_domain_id INT NOT NULL,
                flag_id INT NOT NULL,
                PRIMARY KEY(product_domain_id, flag_id)
            )');
        $this->sql('CREATE INDEX IDX_55DA4B077FFFB868 ON product_domain_flags (product_domain_id)');
        $this->sql('CREATE INDEX IDX_55DA4B07919FE4E5 ON product_domain_flags (flag_id)');
        $this->sql('
            ALTER TABLE
                product_domain_flags
            ADD
                CONSTRAINT FK_55DA4B077FFFB868 FOREIGN KEY (product_domain_id) REFERENCES product_domains (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_domain_flags
            ADD
                CONSTRAINT FK_55DA4B07919FE4E5 FOREIGN KEY (flag_id) REFERENCES flags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
