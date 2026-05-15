<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20251231124034 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (
                queue_name, available_at, delivered_at,
                id
            )');
        $this->sql('DROP INDEX IF EXISTS IDX_75EA56E016BA31DB');
        $this->sql('DROP INDEX IF EXISTS IDX_75EA56E0E3BD61CE');
        $this->sql('DROP INDEX IF EXISTS IDX_75EA56E0FB7336F0');
    }
}
