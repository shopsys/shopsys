<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240801095527 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE complaint_number_sequences (
                id INT NOT NULL,
                number NUMERIC(10, 0) NOT NULL,
                PRIMARY KEY(id)
            )');

        $complaintNumberSequence = $this->sqlQuery('SELECT count(*) FROM complaint_number_sequences')->fetchOne();

        if ($complaintNumberSequence > 0) {
            return;
        }

        $this->sql('INSERT INTO complaint_number_sequences (id, number) VALUES (1, 0)');
    }
}
