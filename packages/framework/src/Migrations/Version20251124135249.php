<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20251124135249 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO order_statuses (type) VALUES (:type)', [
            'type' => OrderStatusTypeEnum::TYPE_WITHDRAWN,
        ]);

        $orderStatusId = $this->connection->lastInsertId();

        foreach ($this->getAllLocales() as $locale) {
            $this->sql(
                'INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:id, :name, :locale)',
                [
                    'id' => $orderStatusId,
                    'name' => t('Withdrawn', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
                    'locale' => $locale,
                ],
            );
        }

        $mailTemplateName = 'order_status_' . $orderStatusId;

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail, order_status_id, subject, body)
                 VALUES (:name, :domainId, :sendMail, :orderStatusId, :subject, :body)',
                [
                    'name' => $mailTemplateName,
                    'domainId' => $domainId,
                    'sendMail' => true,
                    'orderStatusId' => $orderStatusId,
                    'subject' => t(
                        'Withdrawal from contract - order no. {number}',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $locale,
                    ),
                    'body' => t(
                        '<h1>Withdrawal from contract</h1>
                        <p>Dear customer,</p>
                        <p>We confirm receipt of your withdrawal from the contract for order <strong>{number}</strong>.</p>
                        <p>We will process your withdrawal request and contact you within the following days regarding the next steps, including the return of goods and refund.</p>

                        <h2>Order Details</h2>
                        <p>Order number: <a href="{order_detail_url}">{number}</a></p>

                        {products}

                        <p>You can view your order details at any time: <a href="{order_detail_url}">View order detail</a></p>

                        <p>Best regards,<br />
                        Your e-shop team</p>',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $locale,
                    ),
                ],
            );
        }
    }
}
