<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230509125237 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_video_translations (
                id SERIAL NOT NULL,
                product_video INT NOT NULL,
                description VARCHAR(255) NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_95924E34DD9BA170 ON product_video_translations (product_video)');
        $this->sql('
            ALTER TABLE
                product_video_translations
            ADD
                CONSTRAINT FK_95924E34DD9BA170 FOREIGN KEY (product_video) REFERENCES product_videos (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
