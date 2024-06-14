<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240613235059 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $dropForeignKeysQueries = $this->sql(
            '
            SELECT string_agg(\'ALTER TABLE \' || quote_ident(c.relname) || \' DROP CONSTRAINT \' || quote_ident(con.conname), \'; \')
            FROM pg_constraint con
            JOIN pg_class c ON con.conrelid = c.oid
            WHERE c.relname = \'closed_day_excluded_stores\' AND con.contype = \'f\';',
        )->fetchOne();

        foreach (explode('; ', $dropForeignKeysQueries) as $dropForeignKeyQuery) {
            $this->sql($dropForeignKeyQuery);
        }

        $this->sql('ALTER TABLE closed_day_excluded_stores ADD CONSTRAINT FK_B4EC517608F9E8F FOREIGN KEY (closed_day_id) REFERENCES closed_days (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE closed_day_excluded_stores ADD CONSTRAINT FK_B4EC517B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
