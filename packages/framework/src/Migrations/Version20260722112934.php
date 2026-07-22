<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260722112934 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE cart_item_additional_services (
                cart_item_id INT NOT NULL,
                additional_service_id INT NOT NULL,
                PRIMARY KEY (
                    cart_item_id, additional_service_id
                )
            )');
        $this->sql('CREATE INDEX IDX_F63F1CA2E9B59A59 ON cart_item_additional_services (cart_item_id)');
        $this->sql('CREATE INDEX IDX_F63F1CA2F8E98E09 ON cart_item_additional_services (additional_service_id)');
        $this->sql('
            ALTER TABLE
                cart_item_additional_services
            ADD
                CONSTRAINT FK_F63F1CA2E9B59A59 FOREIGN KEY (cart_item_id) REFERENCES cart_items (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('
            ALTER TABLE
                cart_item_additional_services
            ADD
                CONSTRAINT FK_F63F1CA2F8E98E09 FOREIGN KEY (additional_service_id) REFERENCES additional_services (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('ALTER TABLE order_items ADD additional_service_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                order_items
            ADD
                CONSTRAINT FK_62809DB0F8E98E09 FOREIGN KEY (additional_service_id) REFERENCES additional_services (id) ON DELETE
            SET
                NULL NOT DEFERRABLE');
        $this->sql('CREATE INDEX IDX_62809DB0F8E98E09 ON order_items (additional_service_id)');
    }
}
