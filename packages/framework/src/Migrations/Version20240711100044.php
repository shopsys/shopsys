<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240711100044 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE customer_user_role_group_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_7DF8D8A32C2AC5D3 ON customer_user_role_group_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX customer_user_role_group_translations_uniq_trans ON customer_user_role_group_translations (translatable_id, locale)');

        $this->sql('CREATE TABLE customer_user_role_groups (id SERIAL NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->sql('INSERT INTO customer_user_role_groups (roles) VALUES (\'["ROLE_API_ALL"]\')');
        $customerUserRoleGroupId = $this->connection->lastInsertId();

        foreach ($this->getAllLocales() as $locale) {
            $this->sql('INSERT INTO customer_user_role_group_translations (translatable_id, name, locale) VALUES (' . $customerUserRoleGroupId . ', \'Owner\', \'' . $locale . '\')');
        }

        $this->sql('
            ALTER TABLE
                customer_user_role_group_translations
            ADD
                CONSTRAINT FK_7DF8D8A32C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES customer_user_role_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('ALTER TABLE customer_users ADD role_group_id INT DEFAULT ' . $customerUserRoleGroupId . ' NOT NULL');
        $this->sql('ALTER TABLE customer_users ALTER role_group_id DROP DEFAULT');

        $this->sql('
            ALTER TABLE
                customer_users
            ADD
                CONSTRAINT FK_DAB6D0D2D4873F76 FOREIGN KEY (role_group_id) REFERENCES customer_user_role_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_DAB6D0D2D4873F76 ON customer_users (role_group_id)');

        $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'customerUserDefaultGroupRoleId\', 0, ' . $customerUserRoleGroupId . ', \'integer\')');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
