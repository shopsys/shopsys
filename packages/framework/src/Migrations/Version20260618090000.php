<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\ProductQuestion\Mail\ProductQuestionMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260618090000 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->insertMailTemplateIfNotExist(ProductQuestionMail::ADMIN_MAIL_TEMPLATE_NAME);
        $this->insertMailTemplateIfNotExist(ProductQuestionMail::CUSTOMER_MAIL_TEMPLATE_NAME);

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplate(
                ProductQuestionMail::ADMIN_MAIL_TEMPLATE_NAME,
                t('New product question from {customerName}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $this->wrapMailTemplateBodyForGrapesJs(
                    t(
                        '<p>A customer has asked a question about a product.</p> <p><strong>Name:</strong> {customerName}<br> <strong>Email:</strong> {email}<br> <strong>Product:</strong> <a href="{productUrl}" tabindex="0">{productName}</a></p> <p><strong>Question:</strong><br>{question}</p>',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $domainLocale,
                    ),
                ),
                $domainId,
            );

            $this->updateMailTemplate(
                ProductQuestionMail::CUSTOMER_MAIL_TEMPLATE_NAME,
                t('Your question about {productName}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $this->wrapMailTemplateBodyForGrapesJs(
                    t(
                        '<p>Dear {customerName},</p> <p>Thank you for your question about <a href="{productUrl}" tabindex="0">{productName}</a>. We have received it and will get back to you by email as soon as possible.</p> <p><strong>Your question:</strong><br>{question}</p>',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $domainLocale,
                    ),
                ),
                $domainId,
            );
        }
    }

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
