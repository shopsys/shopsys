<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180301081843 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, bcc_email, subject, body, send_mail) VALUES
                (:mailTemplateName, :domainId, null, :subject, :body, true);',
                [
                    'mailTemplateName' => MailTemplate::PERSONAL_DATA_ACCESS_NAME,
                    'domainId' => $domainId,
                    'subject' => t(
                        'Personal information overview - {domain}',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $locale,
                    ),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t('
                                Dear customer,<br/>
                                <br/>
                                based on your email {email}, we are sending you a link to your personal details. By clicking on the link below, you will be taken to a page listing all thepersonal details which we have in evidence in our online store {domain}.<br/>
                                <br/>
                                To overview your personal information please click <a href="{url}" tabindex="0">here</a>.<br/>
                                The link is valid for next 24 hours.<br/>
                                <br/>
                                Best regards<br />
                                Team of {domain}
                            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    ),
                ],
            );
        }
    }
}
