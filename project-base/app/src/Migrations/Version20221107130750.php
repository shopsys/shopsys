<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20221107130750 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE wishlist_items (
                id SERIAL NOT NULL,
                product_id INT NOT NULL,
                wishlist_id INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_B5BB81B54584665A ON wishlist_items (product_id)');
        $this->sql('CREATE INDEX IDX_B5BB81B5FB8E54CD ON wishlist_items (wishlist_id)');
        $this->sql('
            CREATE TABLE wishlists (
                id SERIAL NOT NULL,
                customer_user_id INT DEFAULT NULL,
                uuid UUID NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_4A4C2E1BD17F50A6 ON wishlists (uuid)');
        $this->sql('CREATE INDEX IDX_4A4C2E1BBBB3772B ON wishlists (customer_user_id)');
        $this->sql('
            ALTER TABLE
                wishlist_items
            ADD
                CONSTRAINT FK_B5BB81B54584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                wishlist_items
            ADD
                CONSTRAINT FK_B5BB81B5FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlists (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                wishlists
            ADD
                CONSTRAINT FK_4A4C2E1BBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
