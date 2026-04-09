<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\TwoFactorAuthenticationMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250526101812 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->insertMailTemplateIfNotExist(TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE);

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $domainId = $domainConfig->getId();

            $this->sql(
                'UPDATE mail_templates SET subject = :subject, body = :body
                 WHERE name = :mailTemplateName AND domain_id = :domainId',
                [
                    'mailTemplateName' => TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE,
                    'domainId' => $domainId,
                    'subject' => t('Authentication code', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('
                            Dear customer,<br/>
                            <br/>
                            your two factor authentication code is: {authentication_code}<br/>
                            <br/>
                            Best regards
                        ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    ),
                ],
            );
        }
    }
}
