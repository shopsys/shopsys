<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20170418094333 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            ALTER TABLE payment_prices
                DROP CONSTRAINT FK_C1F3F6CF38248176,
                ADD CONSTRAINT FK_C1F3F6CF38248176 FOREIGN KEY (currency_id)
                    REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->sql('
            ALTER TABLE transport_prices
                DROP CONSTRAINT FK_573018D038248176,
                ADD CONSTRAINT FK_573018D038248176 FOREIGN KEY (currency_id)
                    REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }
}
