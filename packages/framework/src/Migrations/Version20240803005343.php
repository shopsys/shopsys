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
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
