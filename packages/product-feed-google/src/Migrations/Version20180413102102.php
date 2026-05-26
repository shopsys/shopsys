<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180413102102 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $oldTableExists = $this->sqlQuery(
            'SELECT COUNT(*) > 0 FROM information_schema.tables WHERE table_name=\'plugin_data_values\'',
        )->fetchOne();

        if ($oldTableExists) {
            $this->migrateProducts();
        }
    }

    private function migrateProducts(): void
    {
        $rows = $this->sqlQuery(
            'SELECT key, json_value
            FROM plugin_data_values
            WHERE plugin_name=:plugin_name AND context=:context',
            [
                'plugin_name' => 'Shopsys\\ProductFeed\\GoogleBundle\\ShopsysProductFeedGoogleBundle',
                'context' => 'product',
            ],
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $jsonData = json_decode($row['json_value'], true);

            foreach ($jsonData['show'] ?? [] as $domainId => $show) {
                $this->sql(
                    'INSERT INTO google_product_domains (product_id, domain_id, show) 
                        VALUES (:product_id, :domain_id, :show)',
                    [
                        'product_id' => $row['key'],
                        'domain_id' => $domainId,
                        'show' => $show ? 'true' : 'false',
                    ],
                );
            }
        }
    }
}
