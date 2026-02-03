<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240813183535 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $allDomainIds = implode(',', $this->getAllDomainIds());

        $this->sql('ALTER TABLE administrators ADD display_only_domain_ids TEXT DEFAULT \'' . $allDomainIds . '\' NOT NULL');
        $this->sql('COMMENT ON COLUMN administrators.display_only_domain_ids IS \'(DC2Type:simple_array)\'');

        $this->sql('ALTER TABLE administrators ALTER display_only_domain_ids DROP DEFAULT');
    }
}
