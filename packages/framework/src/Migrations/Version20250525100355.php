<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250525100355 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE ai_models (
                id VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                is_active BOOLEAN DEFAULT true NOT NULL,
                is_deprecated BOOLEAN DEFAULT false NOT NULL,
                api_source VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
    }
}
