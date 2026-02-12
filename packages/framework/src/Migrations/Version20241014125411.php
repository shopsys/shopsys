<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241014125411 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('INSERT INTO blog_category_domains (blog_category_id, domain_id, enabled, visible) VALUES (1, :domain_id, true, true) ON CONFLICT DO NOTHING', [
                'domain_id' => $domainId,
            ]);
        }
    }
}
