<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180702111018 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $orderNumberSequenceCount = $this->sql('SELECT count(*) FROM order_number_sequences')->fetchOne();

        if ($orderNumberSequenceCount > 0) {
            return;
        }

        $this->sql('INSERT INTO order_number_sequences (id, number) VALUES (1, 0)');
    }
}
