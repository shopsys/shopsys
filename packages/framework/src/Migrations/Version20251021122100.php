<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20251021122100 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist(WithdrawalMail::MAIL_TEMPLATE_NAME);

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $this->updateMailTemplate(
                WithdrawalMail::MAIL_TEMPLATE_NAME,
                t(
                    'Withdrawal from contract - order no. {number}',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                t(
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
                $domainId,
            );
        }
    }

    /**
     * @param string $mailTemplateName
     */
    private function createMailTemplateIfNotExist(string $mailTemplateName): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $mailTemplateExists = (bool)$this->sql(
                'SELECT EXISTS(SELECT 1 FROM mail_templates WHERE name = :mailTemplateName and domain_id = :domainId)',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                ],
            )->fetchOne();

            if ($mailTemplateExists) {
                continue;
            }

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                    'sendMail' => true,
                ],
            );
        }
    }

    /**
     * @param string $mailTemplateName
     * @param string $subject
     * @param string $body
     * @param int $domainId
     */
    private function updateMailTemplate(string $mailTemplateName, string $subject, string $body, int $domainId): void
    {
        $this->sql(
            'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
            [
                'subject' => $subject,
                'body' => $body,
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
            ],
        );
    }
}
