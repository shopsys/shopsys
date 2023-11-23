<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20231101182132 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->setPageContent(
                'paymentSuccessfulText',
                t('
                <p>
                    Payment for order number {number} has been successful. <br /><br />
                    <a href="{order_detail_url}">Track</a> the status of your order. <br />
                    {transport_instructions}
                </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getDomainLocale($domainId)),
                $domainId,
            );

            $this->setPageContent(
                'paymentFailedText',
                t('
                <p>
                    Payment for order number {number} has failed. <br /><br />
                    Please contact us to resolve the issue.
                </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getDomainLocale($domainId)),
                $domainId,
            );
        }
    }

    /**
     * @param string $settingName
     * @param string $pageContent
     * @param int $domainId
     */
    private function setPageContent(string $settingName, string $pageContent, int $domainId): void
    {
        $paymentSuccessfulPageContent = $this->sql(
            'SELECT COUNT(*) FROM setting_values WHERE name = :settingName AND domain_id = :domainId;',
            [
                'settingName' => $settingName,
                'domainId' => $domainId,
            ],
        )->fetchOne();

        if ($paymentSuccessfulPageContent > 0) {
            return;
        }

        $this->sql(
            'INSERT INTO setting_values (name, domain_id, value, type) VALUES (:settingName, :domainId, :settingValue, \'string\')',
            [
                'settingName' => $settingName,
                'settingValue' => $pageContent,
                'domainId' => $domainId,
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
