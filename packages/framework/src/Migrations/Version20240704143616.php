<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240704143616 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220221121108')) {
            $this->sql('
            CREATE TABLE administrator_role_groups (
                id SERIAL NOT NULL,
                name VARCHAR(100) NOT NULL,
                roles JSON NOT NULL,
                PRIMARY KEY(id)
            )');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220222103841')) {
            $this->sql('ALTER TABLE administrators ADD role_group_id INT DEFAULT NULL');
            $this->sql('
            ALTER TABLE
                administrators
            ADD
                CONSTRAINT FK_73A716FD4873F76 FOREIGN KEY (role_group_id) REFERENCES administrator_role_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_73A716FD4873F76 ON administrators (role_group_id)');
            $this->sql('CREATE UNIQUE INDEX UNIQ_2D0D81B55E237E06 ON administrator_role_groups (name)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220506130850')) {
            $this->sql('ALTER TABLE customer_user_refresh_token_chain ADD administrator_id INT DEFAULT NULL');
            $this->sql('
            ALTER TABLE
                customer_user_refresh_token_chain
            ADD
                CONSTRAINT FK_DA9A5BFD4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_DA9A5BFD4B09E92C ON customer_user_refresh_token_chain (administrator_id)');
            $this->sql('ALTER TABLE administrators ADD uuid UUID NOT NULL DEFAULT uuid_generate_v4()');
            $this->sql('ALTER TABLE administrators ALTER uuid DROP DEFAULT;');
            $this->sql('CREATE UNIQUE INDEX UNIQ_73A716FD17F50A6 ON administrators (uuid)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210329202020')) {
            $this->sql('ALTER TABLE administrators ADD two_factor_authentication_type VARCHAR(32)');
            $this->sql('ALTER TABLE administrators ADD email_authentication_code VARCHAR(16)');
            $this->sql('ALTER TABLE administrators ADD google_authenticator_secret VARCHAR(255)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220713071440')) {
            $this->sql('UPDATE billing_addresses SET street = \'\' WHERE street IS NULL');
            $this->sql('ALTER TABLE billing_addresses ALTER street SET NOT NULL');

            $this->sql('UPDATE billing_addresses SET city = \'\' WHERE city IS NULL');
            $this->sql('ALTER TABLE billing_addresses ALTER city SET NOT NULL');

            $this->sql('UPDATE billing_addresses SET postcode = \'\' WHERE postcode IS NULL');
            $this->sql('ALTER TABLE billing_addresses ALTER postcode SET NOT NULL');

            $this->sql('UPDATE billing_addresses SET country_id = (SELECT min(id) FROM countries) WHERE country_id IS NULL');
            $this->sql('ALTER TABLE billing_addresses ALTER country_id SET NOT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20201006052634')) {
            $this->sql('ALTER TABLE billing_addresses ADD activated BOOLEAN NOT NULL DEFAULT true');
            $this->sql('ALTER TABLE billing_addresses ALTER activated DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210914112021')) {
            $this->sql('ALTER TABLE delivery_addresses ADD uuid UUID DEFAULT NULL');
            $this->sql('UPDATE delivery_addresses SET uuid = uuid_generate_v4()');
            $this->sql('ALTER TABLE delivery_addresses ALTER uuid SET NOT NULL');
            $this->sql('CREATE UNIQUE INDEX UNIQ_2BAF3984D17F50A6 ON delivery_addresses (uuid)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200113093317')) {
            $this->sql('ALTER TABLE customer_users ADD newsletter_subscription BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE customer_users ALTER newsletter_subscription DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200915045504')) {
            $this->sql('DROP INDEX name_domain');
            $this->sql('ALTER TABLE mail_templates ADD transport_id INT DEFAULT NULL');
            $this->sql('ALTER TABLE mail_templates ADD payment_id INT DEFAULT NULL');
            $this->sql('ALTER TABLE mail_templates ADD order_stock_status VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE mail_templates
            ADD CONSTRAINT FK_17F263ED9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('ALTER TABLE mail_templates
            ADD CONSTRAINT FK_17F263ED4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_17F263ED9909C13F ON mail_templates (transport_id)');
            $this->sql('CREATE INDEX IDX_17F263ED4C3A3BB ON mail_templates (payment_id)');
            $this->sql('CREATE UNIQUE INDEX name_domain ON mail_templates (
                name, domain_id, transport_id, payment_id, order_stock_status
            )');
            $this->sql('ALTER TABLE orders ADD stock_status VARCHAR(32) DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200924163010')) {
            $this->sql('DELETE FROM mail_templates WHERE order_stock_status IS NOT NULL');
            $this->sql('ALTER TABLE orders DROP stock_status');
            $this->sql('DROP INDEX name_domain');
            $this->sql('ALTER TABLE mail_templates DROP order_stock_status');
            $this->sql('ALTER TABLE mail_templates ADD order_status_id INT DEFAULT NULL');
            $this->sql('ALTER TABLE mail_templates 
            ADD CONSTRAINT FK_17F263EDD7707B45 FOREIGN KEY (order_status_id) REFERENCES order_statuses (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_17F263EDD7707B45 ON mail_templates (order_status_id)');
            $this->sql('
            CREATE UNIQUE INDEX name_domain ON mail_templates (
                name, domain_id, transport_id, payment_id,
                order_status_id
            )');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230306072255')) {
            $this->sql('DROP INDEX name_domain');
            $this->sql('CREATE UNIQUE INDEX name_domain ON mail_templates (name, domain_id, order_status_id)');
            $this->sql('ALTER TABLE mail_templates DROP COLUMN transport_id');
            $this->sql('ALTER TABLE mail_templates DROP COLUMN payment_id');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230405123121')) {
            $this->sql('DROP INDEX name_domain');
            $this->sql('CREATE UNIQUE INDEX name_domain ON mail_templates (name, domain_id)');

            $this->sql('UPDATE mail_templates SET order_status_id = 1 WHERE name = \'order_status_1\'');
            $this->sql('UPDATE mail_templates SET order_status_id = 2 WHERE name = \'order_status_2\'');
            $this->sql('UPDATE mail_templates SET order_status_id = 3 WHERE name = \'order_status_3\'');
            $this->sql('UPDATE mail_templates SET order_status_id = 4 WHERE name = \'order_status_4\'');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220808131537')) {
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
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
