<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250404110544 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    private const string SETTING_NAME = 'paymentInProcessText';

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $paymentInProcessPageContent = $this->sqlQuery(
                'SELECT COUNT(*) FROM setting_values WHERE name = :settingName AND domain_id = :domainId;',
                [
                    'settingName' => self::SETTING_NAME,
                    'domainId' => $domainId,
                ],
            )->fetchOne();

            if ($paymentInProcessPageContent > 0) {
                return;
            }

            $pageContent = t(
                '<p>You’ve successfully selected a payment method. We’re now waiting for confirmation from the payment provider.</p>
                <p>If you haven’t completed the payment yet, please check your email for payment instructions.</p>
                <p><a href="{order_detail_url}" tabindex="0">Show order detail</a></p>',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );

            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) VALUES (:settingName, :domainId, :settingValue, \'string\')',
                [
                    'settingName' => self::SETTING_NAME,
                    'settingValue' => $pageContent,
                    'domainId' => $domainId,
                ],
            );
        }
    }
}
