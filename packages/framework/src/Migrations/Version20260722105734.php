<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260722105734 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_additional_service_domains (
                domain_id INT NOT NULL,
                product_id INT NOT NULL,
                additional_service_id INT NOT NULL,
                PRIMARY KEY (
                    product_id, additional_service_id,
                    domain_id
                )
            )');
        $this->sql('CREATE INDEX IDX_F7FC4DE24584665A ON product_additional_service_domains (product_id)');
        $this->sql('CREATE INDEX IDX_F7FC4DE2F8E98E09 ON product_additional_service_domains (additional_service_id)');
        $this->sql('
            ALTER TABLE
                product_additional_service_domains
            ADD
                CONSTRAINT FK_F7FC4DE24584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('
            ALTER TABLE
                product_additional_service_domains
            ADD
                CONSTRAINT FK_F7FC4DE2F8E98E09 FOREIGN KEY (additional_service_id) REFERENCES additional_services (id) ON DELETE CASCADE NOT DEFERRABLE');
    }
}
