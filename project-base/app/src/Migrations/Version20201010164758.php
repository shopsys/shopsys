<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;

use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20201010164758 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            if ($domainId === 2) {
                $subject = 'Dokončenie registrácie';
                $body = 'Vážený zákazník,<br /><br />na tomto odkaze môžete dokončiť registráciu a nastaviť si svoje nové heslo: <a href="{activation_url}">{activation_url}</a>';
            } else {
                $subject = 'Dokončení registrace';
                $body = 'Vážený zákazníku,<br /><br />na tomto odkazu můžete dokončit registraci a nastavit si své nové heslo: <a href="{activation_url}">{activation_url}</a>';
            }
            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail, subject, body) 
                VALUES (:mailTemplateName, :domainId, :sendMail, :subject, :body)',
                [
                    'mailTemplateName' => 'customer_activation',
                    'domainId' => $domainId,
                    'sendMail' => true,
                    'subject' => $subject,
                    'body' => $body,
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
}
