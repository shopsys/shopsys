<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191121145700 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $contactFormMainTextCount = $this->sql(
                'SELECT COUNT(*) FROM setting_values WHERE name = \'contactFormMainText\' AND domain_id = :domainId;',
                [
                    'domainId' => $domainId,
                ]
            )->fetchOne();
            if ($contactFormMainTextCount > 0) {
                continue;
            }

            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'contactFormMainText\', :domainId, :text, \'string\')',
                [
                    'domainId' => $domainId,
                    'text' => 'Hi there, our team is happy and ready to answer your question. Please fill out the form below and we will get in touch as soon as possible.',
                ]
            );
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
