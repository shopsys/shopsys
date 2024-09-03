<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240803005343 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX gopay_payment_method_unique');
        $this->sql('ALTER TABLE gopay_payment_methods ADD domain_id INT DEFAULT NULL');
        $this->sql('CREATE UNIQUE INDEX gopay_payment_method_unique ON gopay_payment_methods (domain_id, identifier)');

        foreach ($this->getAllDomainIds() as $domainId) {
            $locale = $this->getDomainLocale($domainId);

            $this->sql(
                'UPDATE gopay_payment_methods SET domain_id = :domainId WHERE LOWER(name) LIKE \'[' . strtolower($locale) . ']%\'',
                ['domainId' => $domainId],
            );
        }
        $this->sql('ALTER TABLE gopay_payment_methods ALTER domain_id SET NOT NULL');

        $this->sql('ALTER TABLE payment_domains ADD go_pay_payment_method_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                payment_domains
            ADD
                CONSTRAINT FK_9532B177B1E3A4E9 FOREIGN KEY (go_pay_payment_method_id) REFERENCES gopay_payment_methods (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_9532B177B1E3A4E9 ON payment_domains (go_pay_payment_method_id)');
        $this->sql('UPDATE payment_domains 
            SET go_pay_payment_method_id = payments.go_pay_payment_method_id 
            FROM payments 
            WHERE payment_domains.payment_id = payments.id AND payment_domains.enabled = TRUE
        ');
        $this->sql('ALTER TABLE payments DROP COLUMN go_pay_payment_method_id');

        $this->sql('ALTER TABLE payment_domains ADD hidden_by_go_pay BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('UPDATE payment_domains 
            SET hidden_by_go_pay = payments.hidden_by_go_pay 
            FROM payments 
            WHERE payment_domains.payment_id = payments.id AND payment_domains.enabled = TRUE
        ');
        $this->sql('ALTER TABLE payment_domains ALTER COLUMN hidden_by_go_pay DROP DEFAULT ');
        $this->sql('ALTER TABLE payments DROP COLUMN hidden_by_go_pay');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
