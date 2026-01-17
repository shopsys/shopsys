<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241104151802 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE price_lists (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                last_update TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                valid_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                valid_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');

        $this->sql('COMMENT ON COLUMN price_lists.last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN price_lists.valid_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN price_lists.valid_to IS \'(DC2Type:datetime_immutable)\'');

        $this->sql('
            CREATE TABLE price_list_product_prices (
                id SERIAL NOT NULL,
                product_id INT NOT NULL,
                price_list_id INT NOT NULL,
                price_amount NUMERIC(20, 6) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_418F3D234584665A ON price_list_product_prices (product_id)');
        $this->sql('CREATE INDEX IDX_418F3D235688DED7 ON price_list_product_prices (price_list_id)');
        $this->sql('COMMENT ON COLUMN price_list_product_prices.price_amount IS \'(DC2Type:money)\'');
        $this->sql('
            ALTER TABLE
                price_list_product_prices
            ADD
                CONSTRAINT FK_418F3D234584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                price_list_product_prices
            ADD
                CONSTRAINT FK_418F3D235688DED7 FOREIGN KEY (price_list_id) REFERENCES price_lists (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
