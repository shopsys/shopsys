<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200403122227 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_type_domains (
                id SERIAL NOT NULL,
                product_type_id INT NOT NULL,
                domain_id INT NOT NULL,
                free_transport_minimal_price NUMERIC(20, 6) DEFAULT NULL,
                free_transport BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('COMMENT ON COLUMN product_type_domains.free_transport_minimal_price IS \'(DC2Type:money)\'');

        $this->sql('CREATE INDEX IDX_29FEF20314959723 ON product_type_domains (product_type_id)');
        $this->sql('CREATE UNIQUE INDEX product_type_domain ON product_type_domains (product_type_id, domain_id)');
        $this->sql('
            ALTER TABLE
                product_type_domains
            ADD
                CONSTRAINT FK_29FEF20314959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $allDomainIds = $this->getAllDomainIds();
        $productTypes = $this->sql('SELECT id FROM product_types')->fetchAll();

        foreach ($productTypes as $productType) {
            $productTypeId = $productType['id'];

            $freeTransport = 'true';
            if ($productTypeId % 2 === 0) {
                $freeTransport = 'false';
            }

            foreach ($allDomainIds as $domainId) {
                $this->sql(
                    sprintf('INSERT INTO "product_type_domains" ("product_type_id", "domain_id", "free_transport", "free_transport_minimal_price") 
                VALUES (%d, %d, %s, 10000)', $productTypeId, $domainId, $freeTransport)
                );
            }
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
