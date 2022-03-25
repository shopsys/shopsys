<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191108140919 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD vat_id INT NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE product_domains ALTER vat_id DROP DEFAULT;');
        $this->sql('
            ALTER TABLE
                product_domains
            ADD
                CONSTRAINT FK_5DA2A42DB5B63A6B FOREIGN KEY (vat_id) REFERENCES vats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_5DA2A42DB5B63A6B ON product_domains (vat_id)');

        $this->migrateCurrentData();
        $this->sql('ALTER TABLE products DROP vat_id');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    private function migrateCurrentData(): void
    {
        $currentProductsIdWithVat = $this->sql('SELECT id, vat_id FROM products')->fetchAllAssociative();

        foreach ($this->getAllDomainIds() as $domainId) {
            foreach ($currentProductsIdWithVat as $currentProductIdWithVat) {
                $newVatId = $currentProductIdWithVat['vat_id'];
                if ($domainId > 1) {
                    $newVatId = $this
                        ->sql(
                            'SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId',
                            [
                                'tmpOriginalId' => $currentProductIdWithVat['vat_id'],
                                'domainId' => $domainId,
                            ]
                        )
                        ->fetchOne();
                }

                $this->sql(
                    'UPDATE product_domains SET vat_id = :vatId WHERE product_id = :productId and domain_id = :domainId',
                    [
                        'vatId' => $newVatId,
                        'productId' => $currentProductIdWithVat['id'],
                        'domainId' => $domainId,
                    ]
                );
            }
        }
    }
}
