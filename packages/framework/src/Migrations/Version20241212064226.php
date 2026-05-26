<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241212064226 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->insertMailTemplateIfNotExist(ResetPasswordMail::MAIL_TEMPLATE_NAME);

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplate(
                ResetPasswordMail::MAIL_TEMPLATE_NAME,
                t('Administrator reset password request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $this->wrapMailTemplateBodyForGrapesJs(
                    t('
                        Dear administrator,<br/><br/>
                        you can set a new password following this <a href="{new_password_url}" tabindex="0">link</a>.<br/><br/>
                        Best regards
                    ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
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
