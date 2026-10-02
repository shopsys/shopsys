<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260909100000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->moveSettingToHomepageSeoPage('seoTitleMainPage', 'seo_title', $domainId);
            $this->moveSettingToHomepageSeoPage('seoMetaDescriptionMainPage', 'seo_meta_description', $domainId);
        }

        $this->sql('DELETE FROM setting_values WHERE name IN (\'seoTitleMainPage\', \'seoMetaDescriptionMainPage\')');
    }

    /**
     * The setting value is used only when the homepage SEO page has no value of its own yet
     */
    private function moveSettingToHomepageSeoPage(
        string $settingName,
        string $seoPageDomainColumn,
        int $domainId,
    ): void {
        $this->sql(
            sprintf(
                'UPDATE seo_page_domains
                SET %1$s = COALESCE(%1$s, (SELECT value FROM setting_values WHERE name = :settingName AND domain_id = :domainId))
                WHERE domain_id = :domainId AND page_slug = :homepageSlug',
                $seoPageDomainColumn,
            ),
            [
                'settingName' => $settingName,
                'domainId' => $domainId,
                'homepageSlug' => '/',
            ],
        );
    }
}
