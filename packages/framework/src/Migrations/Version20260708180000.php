<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260708180000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE carts ADD currency_code VARCHAR(3) DEFAULT NULL');

        $this->sql('ALTER TABLE orders ADD currency_exchange_rate NUMERIC(20, 6) NOT NULL DEFAULT 1');

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $this->sql(
                'UPDATE orders o SET currency_exchange_rate = ROUND(c_ord.exchange_rate / c_def.exchange_rate, 6)
                FROM currencies c_ord, currencies c_def
                WHERE o.domain_id = :domainId
                    AND c_ord.code = o.currency_code
                    AND c_def.code = :defaultCurrencyCode',
                [
                    'domainId' => $domainConfig->getId(),
                    'defaultCurrencyCode' => $domainConfig->getDefaultCurrencyCode(),
                ],
            );
        }

        $this->sql('ALTER TABLE orders ALTER currency_exchange_rate DROP DEFAULT');
    }
}
