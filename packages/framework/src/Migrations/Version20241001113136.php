<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20241001113136 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist(InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME);
        $this->createMailTemplateIfNotExist(InquiryMail::ADMIN_MAIL_TEMPLATE_NAME);

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplate(
                InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME,
                t('Thank you for your product inquiry', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                t('<div class="gjs-text-ckeditor"><p>Dear {fullName},</p><p>Thank you for your interest in <a data-cke-saved-href="{productUrl}" href="{productUrl}">{productName}</a>.</p><p>We have received your inquiry and will be reaching out to you shortly with further information.</p><p>If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.</p><p>Best regards</p></div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $domainId,
            );

            $this->updateMailTemplate(
                InquiryMail::ADMIN_MAIL_TEMPLATE_NAME,
                t('New product inquiry received', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                t('<div class="gjs-text-ckeditor"><div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;"><p>A new product inquiry has just been received from <strong>{fullName}</strong>.</p><p>Please find the details below:</p><ul style="list-style-type: none; padding: 0;"><li><strong>Customer name:</strong> {fullName}</li><li><strong>Customer email:</strong> <a href="mailto:{email}">{email}</a></li><li><strong>Customer telephone:</strong> <a href="tel:{telephone}">{telephone}</a></li><li><strong>Company name:</strong> {companyName}</li><li><strong>Company number:</strong> {companyNumber}</li><li><strong>Company tax number:</strong> {companyTaxNumber}</li></ul><p><strong>Note from customer:</strong></p><blockquote style="border-left: 4px solid #ccc; margin: 0 0 1em; padding: 0.5em; font-style: italic; background-color: #f9f9f9;">{note}</blockquote><p><strong>Inquired product:</strong></p><p><a href="{productUrl}" style="color: #1a73e8; text-decoration: none;">{productName}</a> (Catalog number: {productCatnum})</p><p><img src="{productImageUrl}" alt="{productName}" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 4px;"></p><p>Please review the inquiry and take the necessary steps to follow up.</p><p><a href="{adminInquiryDetailUrl}" style="background-color: #1a73e8; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Review Inquiry</a></p></div></div>
', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $domainId,
            );
        }
    }

    /**
     * @param string $mailTemplateName
     */
    private function createMailTemplateIfNotExist(
        string $mailTemplateName,
    ): void {
        foreach ($this->getAllDomainIds() as $domainId) {
            $mailTemplateCount = $this->sql(
                'SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName and domain_id = :domainId',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                ],
            )->fetchOne();

            if ($mailTemplateCount !== 0) {
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

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
