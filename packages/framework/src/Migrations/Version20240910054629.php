<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240910054629 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    private const string COMPLAINT_STATUS_1 = 'complaint_status_1';
    private const string COMPLAINT_STATUS_2 = 'complaint_status_2';
    private const string COMPLAINT_STATUS_3 = 'complaint_status_3';

    #[Override]
    public function up(Schema $schema): void
    {
        $this->insertMailTemplateIfNotExist(self::COMPLAINT_STATUS_1);
        $this->insertMailTemplateIfNotExist(self::COMPLAINT_STATUS_2);
        $this->insertMailTemplateIfNotExist(self::COMPLAINT_STATUS_3);

        $this->sql('UPDATE mail_templates SET complaint_status_id = 1 WHERE name = :mailTemplateName', ['mailTemplateName' => self::COMPLAINT_STATUS_1]);
        $this->sql('UPDATE mail_templates SET complaint_status_id = 2 WHERE name = :mailTemplateName', ['mailTemplateName' => self::COMPLAINT_STATUS_2]);
        $this->sql('UPDATE mail_templates SET complaint_status_id = 3 WHERE name = :mailTemplateName', ['mailTemplateName' => self::COMPLAINT_STATUS_3]);

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->sql(
                'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
                [
                    'mailTemplateName' => self::COMPLAINT_STATUS_1,
                    'subject' => t('Status of complaint with number {complaint_number} from order number {order_number} created on {date} has changed', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('Dear customer, <br /><br />Your complaint with number {complaint_number} from order number {order_number} created {date} with preferred resolution {complaint_resolution} is being processed. For more information, visit <a href="{complaint_detail_url}" tabindex="0">complaint detail</a>.<br /><br />Do you need anything else? Visit our <a href="{url}" tabindex="0">website</a>.', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    ),
                    'domainId' => $domainId,
                ],
            );

            $this->sql(
                'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
                [
                    'mailTemplateName' => self::COMPLAINT_STATUS_2,
                    'subject' => t('Status of complaint with number {complaint_number} from order number {order_number} created on {date} has changed', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('Dear customer, <br /><br />Your complaint with number {complaint_number} from order number {order_number} created {date} with preferred resolution {complaint_resolution} has been finished. For more information, visit <a href="{complaint_detail_url}" tabindex="0">complaint detail</a>.<br /><br />Do you need anything else? Visit our <a href="{url}" tabindex="0">website</a>.', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    ),
                    'domainId' => $domainId,
                ],
            );

            $this->sql(
                'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
                [
                    'mailTemplateName' => self::COMPLAINT_STATUS_3,
                    'subject' => t('Status of complaint with number {complaint_number} from order number {order_number} created on {date} has changed', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('Dear customer, <br /><br />Your complaint with number {complaint_number} from order number {order_number} created {date} with preferred resolution {complaint_resolution} is being processed. For more information, visit <a href="{complaint_detail_url}" tabindex="0">complaint detail</a>.<br /><br />Do you need anything else? Visit our <a href="{url}" tabindex="0">website</a>.', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    ),
                    'domainId' => $domainId,
                ],
            );
        }
    }
}
