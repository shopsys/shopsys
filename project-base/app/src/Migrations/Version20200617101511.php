<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200617101511 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE advert_category (
                advert_id INT NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY(advert_id, category_id)
            )');
        $this->sql('CREATE INDEX IDX_84EEA340D07ECCB6 ON advert_category (advert_id)');
        $this->sql('CREATE INDEX IDX_84EEA34012469DE2 ON advert_category (category_id)');
        $this->sql('
            ALTER TABLE
                advert_category
            ADD
                CONSTRAINT FK_84EEA340D07ECCB6 FOREIGN KEY (advert_id) REFERENCES adverts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                advert_category
            ADD
                CONSTRAINT FK_84EEA34012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
