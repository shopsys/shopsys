<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240527121326 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql(
            'UPDATE setting_values SET name = :newName WHERE name = :oldName',
            [
                'oldName' => 'cookiesArticleId',
                'newName' => 'userConsentPolicyArticleId',
            ],
        );

        $renameRoleSql = 'UPDATE administrator_roles SET role = :newRole WHERE role = :oldRole';

        $this->sql(
            $renameRoleSql,
            [
                'oldRole' => 'ROLE_COOKIES_FULL',
                'newRole' => 'ROLE_USER_CONSENT_POLICY_FULL',
            ],
        );

        $this->sql(
            $renameRoleSql,
            [
                'oldRole' => 'ROLE_COOKIES_VIEW',
                'newRole' => 'ROLE_USER_CONSENT_POLICY_VIEW',
            ],
        );

        $renameInRoleGroupSql = 'UPDATE administrator_role_groups
            SET roles = (
                SELECT jsonb_agg(
                    CASE 
                        WHEN elem = :oldRole THEN :newRole 
                        ELSE elem 
                    END
                )
                FROM jsonb_array_elements_text(roles::jsonb) AS elem
            )
            WHERE :oldRole = ANY (SELECT jsonb_array_elements_text(roles::jsonb)::text)';

        $this->sql(
            $renameInRoleGroupSql,
            [
                'oldRole' => 'ROLE_COOKIES_FULL',
                'newRole' => 'ROLE_USER_CONSENT_POLICY_FULL',
            ],
        );

        $this->sql(
            $renameInRoleGroupSql,
            [
                'oldRole' => 'ROLE_COOKIES_VIEW',
                'newRole' => 'ROLE_USER_CONSENT_POLICY_VIEW',
            ],
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
