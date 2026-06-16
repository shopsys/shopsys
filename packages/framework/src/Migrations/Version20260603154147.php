<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260603154147 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders DROP order_payment_status_page_valid_from');
        $this->sql('ALTER TABLE orders DROP order_payment_status_page_validity_hash');

        $this->sql('
            CREATE TABLE payment_return_hashes (
                hash VARCHAR(64) NOT NULL,
                order_id INT NOT NULL,
                expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(hash)
            )');
        $this->sql('CREATE INDEX IDX_A626CBF58D9F6D38 ON payment_return_hashes (order_id)');
        $this->sql('CREATE INDEX IDX_3FA21787D4C6111 ON payment_return_hashes (expires_at)');
        $this->sql('
            ALTER TABLE
                payment_return_hashes
            ADD
                CONSTRAINT FK_3FA217878D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
