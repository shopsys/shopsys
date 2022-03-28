<?php

declare(strict_types=1);

namespace Shopsys\BackendApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220328122719 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE oauth2_authorization_code (
            identifier CHAR(80) NOT NULL, 
            client VARCHAR(32) NOT NULL, 
            expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            user_identifier VARCHAR(128) DEFAULT NULL,
            scopes TEXT DEFAULT NULL,
            revoked BOOLEAN NOT NULL,
            PRIMARY KEY(identifier));
        ');
        $this->sql('CREATE INDEX IDX_509FEF5FC7440455 ON oauth2_authorization_code (client);');
        $this->sql('COMMENT ON COLUMN oauth2_authorization_code.expiry IS \'(DC2Type:datetime_immutable)\';');
        $this->sql('COMMENT ON COLUMN oauth2_authorization_code.scopes IS \'(DC2Type:oauth2_scope)\';');
        $this->sql('ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->sql('ALTER TABLE oauth2_refresh_token DROP CONSTRAINT FK_4DD90732B6A2DD68;');
        $this->sql('ALTER TABLE oauth2_refresh_token ALTER access_token DROP NOT NULL;');
        $this->sql('ALTER TABLE oauth2_refresh_token ALTER expiry TYPE TIMESTAMP(0) WITHOUT TIME ZONE;');
        $this->sql('ALTER TABLE oauth2_refresh_token ALTER expiry DROP DEFAULT;');
        $this->sql('COMMENT ON COLUMN oauth2_refresh_token.expiry IS \'(DC2Type:datetime_immutable)\';');
        $this->sql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->sql('ALTER TABLE oauth2_access_token DROP CONSTRAINT FK_454D9673C7440455;');
        $this->sql('ALTER TABLE oauth2_access_token ALTER expiry TYPE TIMESTAMP(0) WITHOUT TIME ZONE;');
        $this->sql('ALTER TABLE oauth2_access_token ALTER expiry DROP DEFAULT;');
        $this->sql('COMMENT ON COLUMN oauth2_access_token.expiry IS \'(DC2Type:datetime_immutable)\';');
        $this->sql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->sql('ALTER TABLE oauth2_client ADD allow_plain_text_pkce BOOLEAN DEFAULT false NOT NULL;');
        $this->sql('ALTER TABLE oauth2_client ALTER secret DROP NOT NULL;');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
