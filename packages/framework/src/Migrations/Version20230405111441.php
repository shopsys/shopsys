<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230405111441 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $insertToSettingSql = 'INSERT INTO setting_values (name, domain_id, value, type) VALUES (:settingName, :domainId, :settingValue, :settingType)';

        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql(
                $insertToSettingSql,
                [
                    'settingName' => MailSetting::MAIL_WHITELIST,
                    'domainId' => $domainId,
                    'settingValue' => '["/@shopsys\\\\.com$/"]',
                    'settingType' => 'string',
                ],
            );

            $this->sql(
                $insertToSettingSql,
                [
                    'settingName' => MailSetting::MAIL_WHITELIST_ENABLED,
                    'domainId' => $domainId,
                    'settingValue' => 'false',
                    'settingType' => 'boolean',
                ],
            );
        }
    }
}
