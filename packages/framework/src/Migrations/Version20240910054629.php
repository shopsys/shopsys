<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240910054629 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist('complaint_status_1', true, 1);
        $this->createMailTemplateIfNotExist('complaint_status_2', true, 2);

        $this->sql('UPDATE mail_templates SET complaint_status_id = 1 WHERE name = \'complaint_status_1\'');
        $this->sql('UPDATE mail_templates SET complaint_status_id = 2 WHERE name = \'complaint_status_2\'');

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->sql(
                'UPDATE mail_templates SET subject = :body WHERE name = \'complaint_status_1\' AND domain_id = :domainId',
                [
                    'body' => t('Status of complaint with number {complaint_number} from order number {order_number} created on {date} has changed', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    'domainId' => $domainId,
                ],
            );
            $this->sql(
                'UPDATE mail_templates SET body = :body WHERE name = \'complaint_status_1\' AND domain_id = :domainId',
                [
                    'body' => '<div style="box-sizing: border-box; padding: 10px;"><div class="gjs-text-ckeditor">' .
                        t('Dear customer, <br /><br />Your complaint with number {complaint_number} from order number {order_number} created {date} is being processed. For more information, visit <a href="{complaint_detail_url}">complaint detail</a>.<br /><br />Do you need anything else? Visit our <a href="{url}">website</a>.', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale) .
                        '</div></div>',
                    'domainId' => $domainId,
                ],
            );
            $this->sql(
                'UPDATE mail_templates SET subject = :body WHERE name = \'complaint_status_2\' AND domain_id = :domainId',
                [
                    'body' => t('Status of complaint with number {complaint_number} from order number {order_number} created on {date} has changed', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale),
                    'domainId' => $domainId,
                ],
            );
            $this->sql(
                'UPDATE mail_templates SET body = :body WHERE name = \'complaint_status_2\' AND domain_id = :domainId',
                [
                    'body' => '<div style="box-sizing: border-box; padding: 10px;"><div class="gjs-text-ckeditor">' .
                        t('Dear customer, <br /><br />Your complaint with number {complaint_number} from order number {order_number} created {date} has been finished. For more information, visit <a href="{complaint_detail_url}">complaint detail</a>.<br /><br />Do you need anything else? Visit our <a href="{url}">website</a>.', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale) .
                        '</div></div>',
                    'domainId' => $domainId,
                ],
            );
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    /**
     * @param string $mailTemplateName
     * @param bool $sendMail
     * @param int $complaintStatusId
     */
    private function createMailTemplateIfNotExist(
        string $mailTemplateName,
        bool $sendMail,
        int $complaintStatusId,
    ): void {
        $mailTemplateCount = $this->sql('SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName', [
            'mailTemplateName' => $mailTemplateName,
        ])->fetchOne();

        if ($mailTemplateCount <= 0) {
            foreach ($this->getAllDomainIds() as $domainId) {
                $this->sql(
                    'INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)',
                    [
                        'mailTemplateName' => $mailTemplateName,
                        'domainId' => $domainId,
                        'sendMail' => $sendMail,
                        'complaintStatus' => $complaintStatusId,
                    ],
                );
            }
        }
    }
}
