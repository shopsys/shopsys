<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250526101812 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();
            $subject = t('Authentication code', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $body = t('
            Dear customer,<br/>
            <br/>
            your two factor authentication code is: {authentication_code}<br/>
            <br/>
            Best regards
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->createMailTemplateIfNotExist('two_factor_authentication_code', $domainId, $subject, $body);
        }
    }

    private function createMailTemplateIfNotExist(
        string $mailTemplateName,
        int $domainId,
        string $subject,
        string $body,
    ): void {
        $mailTemplateCount = $this->sql(
            'SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName and domain_id = :domainId',
            [
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
            ],
        )->fetchOne();

        if ($mailTemplateCount !== 0) {
            return;
        }

        $this->sql(
            'INSERT INTO mail_templates (name, domain_id, send_mail, subject, body) VALUES (:mailTemplateName, :domainId, :sendMail, :subject, :body)',
            [
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
                'sendMail' => true,
                'subject' => $subject,
                'body' => sprintf('<div style="box-sizing: border-box;"><div class="gjs-text-ckeditor">%s</div></div>', $body),
            ],
        );
    }
}
