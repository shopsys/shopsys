<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20251022164949 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE withdrawal_requests (
            id SERIAL NOT NULL,
            order_id INT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            telephone VARCHAR(30) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            note TEXT DEFAULT NULL,
            requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->sql('COMMENT ON COLUMN withdrawal_requests.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('CREATE UNIQUE INDEX UNIQ_3E7DE8A8D9F6D38 ON withdrawal_requests (order_id)');
        $this->sql('ALTER TABLE withdrawal_requests ADD CONSTRAINT FK_3E7DE8A8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('ALTER TABLE orders ADD withdrawal_request_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE2E695421 FOREIGN KEY (withdrawal_request_id) REFERENCES withdrawal_requests (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE UNIQUE INDEX UNIQ_E52FFDEE2E695421 ON orders (withdrawal_request_id)');
    }
}
