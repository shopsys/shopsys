<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180603135347 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist('order_status_1', 'true');
        $this->createMailTemplateIfNotExist('order_status_2', 'false');
        $this->createMailTemplateIfNotExist('order_status_3', 'false');
        $this->createMailTemplateIfNotExist('order_status_4', 'false');
        $this->createMailTemplateIfNotExist('registration_confirm', 'true');
        $this->createMailTemplateIfNotExist('reset_password', 'true');
    }

    /**
     * @param string $mailTemplateName
     * @param string $sendMail
     */
    private function createMailTemplateIfNotExist($mailTemplateName, $sendMail): void
    {
        $mailTemplateCount = $this->sql('SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName', [
            'mailTemplateName' => $mailTemplateName,
        ])->fetchOne();

        if ($mailTemplateCount <= 0) {
            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, 1, :sendMail)',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'sendMail' => $sendMail,
                ],
            );
        }
    }
}
