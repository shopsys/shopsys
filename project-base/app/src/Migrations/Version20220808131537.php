<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Model\Mail\Setting\MailSetting;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20220808131537 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => MailSetting::MAIL_FACEBOOK_URL,
                'domainId' => $domainId,
                'value' => 'https://facebook.com',
                'type' => 'string',
            ]);
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => MailSetting::MAIL_INSTAGRAM_URL,
                'domainId' => $domainId,
                'value' => 'https://instagram.com',
                'type' => 'string',
            ]);
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => MailSetting::MAIL_YOUTUBE_URL,
                'domainId' => $domainId,
                'value' => 'https://youtube.com',
                'type' => 'string',
            ]);
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => MailSetting::MAIL_LINKEDIN_URL,
                'domainId' => $domainId,
                'value' => 'https://linkedin.com',
                'type' => 'string',
            ]);
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => MailSetting::MAIL_TIKTOK_URL,
                'domainId' => $domainId,
                'value' => 'https://tiktok.com',
                'type' => 'string',
            ]);
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => MailSetting::MAIL_FOOTER_TEXT,
                'domainId' => $domainId,
                'value' => 'Shopsys s.r.o., Koksární 10, 702 00 Ostrava, Česká republika<br />tel.: +420 111 222 333, <br />e-mail: info@shopsys.cz',
                'type' => 'string',
            ]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
