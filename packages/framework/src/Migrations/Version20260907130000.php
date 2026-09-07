<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260907130000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE articles ADD publish_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('ALTER TABLE articles ADD modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('ALTER TABLE blog_articles ADD modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'seoOrganization\', :domainId, \'{}\', \'string\')', ['domainId' => $domainId]);
        }
    }
}
