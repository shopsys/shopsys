<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261119110000 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    public const string REJECTED_COMPLAINT_STATUS_CODE = 'rejected';

    #[Override]
    public function up(Schema $schema): void
    {
        $complaintStatusId = $this->createComplaintStatus();
        $mailTemplateName = 'complaint_status_' . $complaintStatusId;

        $this->insertMailTemplateIfNotExist($mailTemplateName);
        $this->sql('UPDATE mail_templates SET complaint_status_id = :complaintStatusId WHERE name = :mailTemplateName', [
            'complaintStatusId' => $complaintStatusId,
            'mailTemplateName' => $mailTemplateName,
        ]);

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->sql(
                'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'subject' => t('Status of complaint with number {complaint_number} from order number {order_number} created on {date} has changed', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('Dear customer, <br /><br />Your complaint with number {complaint_number} from order number {order_number} created {date} with preferred resolution {complaint_resolution} has been rejected. For more information, visit <a href="{complaint_detail_url}" tabindex="0">complaint detail</a>.<br /><br />Do you need anything else? Visit our <a href="{url}" tabindex="0">website</a>.', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    ),
                    'domainId' => $domainId,
                ],
            );
        }
    }

    private function createComplaintStatus(): int
    {
        $complaintStatusId = $this->sqlQuery(
            'INSERT INTO complaint_statuses (status_type, code) VALUES (:statusType, :code) RETURNING id',
            [
                'statusType' => ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN,
                'code' => self::REJECTED_COMPLAINT_STATUS_CODE,
            ],
        )->fetchOne();

        foreach ($this->getAllLocales() as $locale) {
            $this->sql(
                'INSERT INTO complaint_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)',
                [
                    'translatableId' => $complaintStatusId,
                    'name' => t('Rejected', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
                    'locale' => $locale,
                ],
            );
        }

        return (int)$complaintStatusId;
    }
}
