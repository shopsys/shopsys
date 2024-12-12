<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20241212064226 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist(ResetPasswordMail::MAIL_TEMPLATE_NAME);

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplate(
                ResetPasswordMail::MAIL_TEMPLATE_NAME,
                t('Administrator reset password request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                t('Dear administrator.<br /><br />'
                    . 'You can set a new password following this link: <a href="{new_password_url}">{new_password_url}</a>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
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
