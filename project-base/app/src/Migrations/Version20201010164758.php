<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Migrations\DomainAwareInterface;
use Shopsys\FrameworkBundle\Migrations\MailTemplateMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20201010164758 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail, subject, body)
                VALUES (:mailTemplateName, :domainId, :sendMail, :subject, :body)',
                [
                    'mailTemplateName' => 'customer_activation',
                    'domainId' => $domainId,
                    'sendMail' => true,
                    'subject' => t('Customer activation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('
                            Dear customer,<br/>
                            <br/>
                            you can finish registration and set new password via this <a href="{activation_url}" tabindex="0">link</a>.<br/>
                            <br/>
                            Best regards
                        ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    ),
                ],
            );
        }
    }
}
