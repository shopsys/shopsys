<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalConfirmationMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260814100100 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail, subject, body)
                 VALUES (:name, :domainId, :sendMail, :subject, :body)',
                [
                    'name' => WithdrawalConfirmationMail::ORDER_WITHDRAWAL_CONFIRMATION_NAME,
                    'domainId' => $domainId,
                    'sendMail' => true,
                    'subject' => t('Confirm withdrawal from contract - order no. {order_number}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => $this->wrapMailTemplateBodyForGrapesJs(
                        t(
                            '<p>We have received a request to withdraw from the contract for your order no. {order_number} on {domain}.</p> <p>To confirm the withdrawal request, click the following link: <a href="{url}" tabindex="0">Confirm withdrawal request</a>. The link is valid for 24 hours.</p> <p>If you did not make this request, you can ignore this email.</p>',
                            [],
                            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                            $domainLocale,
                        ),
                    ),
                ],
            );
        }
    }
}
