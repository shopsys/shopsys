<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20241113193905 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'domainId' => $domainId,
                'name' => 'luigisBoxRank',
                'value' => 7,
                'type' => 'integer',
            ]);
        }
    }
}
