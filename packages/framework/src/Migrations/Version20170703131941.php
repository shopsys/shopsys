<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20170703131941 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    public function up(Schema $schema)
    {
        $this->sql('
            CREATE TABLE brand_domains (
                brand_id INT NOT NULL,
                domain_id INT NOT NULL,
                seo_title TEXT DEFAULT NULL,
                seo_meta_description TEXT DEFAULT NULL,
                seo_h1 TEXT DEFAULT NULL,
                PRIMARY KEY(brand_id, domain_id)
            )');
        $this->sql('CREATE INDEX IDX_6B401AE644F5D008 ON brand_domains (brand_id)');
        $this->sql('
            ALTER TABLE
                brand_domains
            ADD
                CONSTRAINT FK_6B401AE644F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $allDomainIds = $this->getAllDomainIds();
        foreach ($allDomainIds as $domainId) {
            $this->sql(
                'INSERT INTO brand_domains (brand_id, domain_id)
				SELECT id AS brand_id, ' . $domainId . ' AS domain_id FROM brands'
            );
        }
    }

    public function down(Schema $schema)
    {
    }
}
