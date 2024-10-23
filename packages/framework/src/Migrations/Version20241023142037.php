<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241023142037 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints ADD customer_id INT DEFAULT NULL');
        $this->sql('UPDATE complaints c SET customer_id = cu.customer_id FROM customer_users cu WHERE cu.id = c.customer_user_id');
        $this->sql(
            '
            ALTER TABLE
                complaints
            ADD
                CONSTRAINT FK_A05AAF3A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE',
        );
        $this->sql('CREATE INDEX IDX_A05AAF3A9395C3F3 ON complaints (customer_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
