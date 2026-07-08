<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260708120000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_manual_input_prices ADD currency_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE price_list_product_prices ADD currency_id INT DEFAULT NULL');

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $parameters = [
                'currencyCode' => $domainConfig->getDefaultCurrencyCode(),
                'domainId' => $domainConfig->getId(),
            ];

            $this->sql(
                'UPDATE product_manual_input_prices pmip SET currency_id = (SELECT id FROM currencies WHERE code = :currencyCode)
                FROM pricing_groups pg
                WHERE pmip.pricing_group_id = pg.id AND pg.domain_id = :domainId',
                $parameters,
            );

            $this->sql(
                'UPDATE price_list_product_prices plpp SET currency_id = (SELECT id FROM currencies WHERE code = :currencyCode)
                FROM price_lists pl
                WHERE plpp.price_list_id = pl.id AND pl.domain_id = :domainId',
                $parameters,
            );
        }

        $this->sql('ALTER TABLE product_manual_input_prices ALTER currency_id SET NOT NULL');
        $this->sql('ALTER TABLE product_manual_input_prices DROP CONSTRAINT product_manual_input_prices_pkey');
        $this->sql('ALTER TABLE product_manual_input_prices ADD PRIMARY KEY (product_id, pricing_group_id, currency_id)');
        $this->sql('ALTER TABLE product_manual_input_prices ADD CONSTRAINT FK_6034D7F838248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('CREATE INDEX IDX_6034D7F838248176 ON product_manual_input_prices (currency_id)');

        $this->sql('ALTER TABLE price_list_product_prices ALTER currency_id SET NOT NULL');
        $this->sql('ALTER TABLE price_list_product_prices ADD CONSTRAINT FK_418F3D2338248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('CREATE INDEX IDX_418F3D2338248176 ON price_list_product_prices (currency_id)');
    }
}
