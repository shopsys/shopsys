<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240130104139 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stores ADD domain_id INT NOT NULL DEFAULT 1');
        $storeDomainsData = $this->sql('SELECT store_id, domain_id FROM store_domains WHERE is_enabled = TRUE ORDER BY domain_id')->fetchAllAssociative();
        foreach ($storeDomainsData as $storeDomainData) {
            $this->sql('UPDATE stores SET domain_id = :domainId WHERE id = :storeId', [
                'domainId' => $storeDomainData['domain_id'],
                'storeId' => $storeDomainData['store_id'],
            ]);
        }
        $this->sql('ALTER TABLE stores ALTER domain_id DROP DEFAULT');
        $this->sql('DROP TABLE store_domains');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
