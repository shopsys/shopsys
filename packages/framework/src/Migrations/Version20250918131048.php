<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250918131048 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_promotion_xy (
                id SERIAL NOT NULL,
                buy_quantity INT NOT NULL,
                free_quantity INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('ALTER TABLE products ADD promotion_xy_id INT DEFAULT NULL');
        $this->sql('CREATE INDEX IDX_B3BA5A5AE0B1DB7A ON products (promotion_xy_id)');

        $this->sql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AE0B1DB7A FOREIGN KEY (promotion_xy_id) REFERENCES product_promotion_xy (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('ALTER TABLE flags ADD promotion_xy_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE flags ADD CONSTRAINT FK_B0541BAE0B1DB7A FOREIGN KEY (promotion_xy_id) REFERENCES product_promotion_xy (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_B0541BAE0B1DB7A ON flags (promotion_xy_id)');
    }
}
