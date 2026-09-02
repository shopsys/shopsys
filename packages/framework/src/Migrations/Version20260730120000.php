<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260730120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_statuses ADD product_reviews_allowed BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE order_statuses ALTER product_reviews_allowed DROP DEFAULT');
        $this->sql('UPDATE order_statuses SET product_reviews_allowed = TRUE WHERE type = :doneType', [
            'doneType' => OrderStatusTypeEnum::TYPE_DONE,
        ]);
    }
}
