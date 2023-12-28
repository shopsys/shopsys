<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231228140243 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $insertToSettingSql = 'INSERT INTO setting_values (name, domain_id, value, type) VALUES (:settingName, :domainId, :settingValue, :settingType)';

        $this->sql(
            $insertToSettingSql,
            [
                'settingName' => SeoSettingFacade::SEO_ALTERNATIVE_DOMAINS,
                'domainId' => 0,
                'settingValue' => json_encode([], JSON_THROW_ON_ERROR),
                'settingType' => 'string',
            ],
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
