<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260710073223 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE cart_gift_vouchers (
                cart_id INT NOT NULL,
                gift_voucher_id INT NOT NULL,
                PRIMARY KEY (cart_id, gift_voucher_id)
            )');
        $this->sql('CREATE INDEX IDX_E108B56C1AD5CDBF ON cart_gift_vouchers (cart_id)');
        $this->sql('CREATE INDEX IDX_E108B56C855BDC84 ON cart_gift_vouchers (gift_voucher_id)');
        $this->sql('
            ALTER TABLE
                cart_gift_vouchers
            ADD
                CONSTRAINT FK_E108B56C1AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->sql('
            ALTER TABLE
                cart_gift_vouchers
            ADD
                CONSTRAINT FK_E108B56C855BDC84 FOREIGN KEY (gift_voucher_id) REFERENCES gift_vouchers (id) ON DELETE CASCADE');
    }
}
