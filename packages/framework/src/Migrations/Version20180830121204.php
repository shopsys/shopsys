<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180830121204 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE users ADD telephone VARCHAR(30) DEFAULT NULL');

        $phonesAndBillingAddressesIds = $this->sqlQuery('SELECT telephone, id FROM billing_addresses')->fetchAllAssociative();

        foreach ($phonesAndBillingAddressesIds as $phoneAndBillingAddressId) {
            $this->sql(
                'UPDATE users
                SET telephone = :telephone
                WHERE billing_address_id = :billing_address_id',
                [
                    'telephone' => $phoneAndBillingAddressId['telephone'],
                    'billing_address_id' => $phoneAndBillingAddressId['id'],
                ],
            );
        }
        $this->sql('ALTER TABLE billing_addresses DROP COLUMN telephone');
    }
}
