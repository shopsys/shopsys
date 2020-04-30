<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200113143308 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE gopay_transactions (
                go_pay_id VARCHAR(20) NOT NULL,
                order_id INT NOT NULL,
                go_pay_status VARCHAR(30) DEFAULT NULL,
                PRIMARY KEY(go_pay_id)
            )');
        $this->sql('CREATE INDEX IDX_B8436D28D9F6D38 ON gopay_transactions (order_id)');
        $this->sql('
            ALTER TABLE
                gopay_transactions
            ADD
                CONSTRAINT FK_B8436D28D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->createDataForExistingTransactions();
    }

    private function createDataForExistingTransactions()
    {
        $data = $this->sql('SELECT id, go_pay_id, go_pay_status FROM orders WHERE go_pay_id IS NOT NULL');
        foreach ($data->fetchAll() as $row) {
            $this->sql(
                'INSERT INTO gopay_transactions (go_pay_id, order_id, go_pay_status) VALUES (:go_pay_id, :order_id, :go_pay_status)',
                [
                    'go_pay_id' => $row['go_pay_id'],
                    'order_id' => $row['id'],
                    'go_pay_status' => $row['go_pay_status'],
                ]
            );
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
