<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250121131930 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230412081031')) {
            $this->sql('
            CREATE TABLE product_videos (
                id SERIAL NOT NULL,
                product_id INT NOT NULL,
                video_token VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_4BD625964584665A ON product_videos (product_id)');
            $this->sql('
            ALTER TABLE
                product_videos
            ADD
                CONSTRAINT FK_4BD625964584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230509125237')) {
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
    }
}
