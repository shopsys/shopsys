<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Migrations\DomainAwareInterface;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241119105039 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'domainId' => $domainId,
                'name' => 'heurekaFeedDeliveryDays',
                'value' => null,
                'type' => 'none',
            ]);
        }
    }
}
