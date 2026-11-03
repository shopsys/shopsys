<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\User;

use Doctrine\DBAL\Connection;

class McpDatabaseRoleRepository
{
    public function __construct(
        protected readonly Connection $defaultConnection,
    ) {
    }

    public function roleExists(string $roleName): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT 1 FROM pg_roles WHERE rolname = ?',
            [$roleName],
        );
    }

    public function canRoleUsePublicSchema(string $roleName): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT has_schema_privilege(?, \'public\', \'USAGE\')',
            [$roleName],
        );
    }

    /**
     * @return list<string>
     */
    public function getPublicSchemaTableNamesReadableByRole(string $roleName): array
    {
        $tableNames = $this->defaultConnection->fetchFirstColumn(
            'SELECT c.relname
            FROM pg_class c
            WHERE c.relnamespace = \'public\'::regnamespace
                AND c.relkind IN (\'r\', \'p\', \'v\', \'m\', \'f\')
                AND has_any_column_privilege(CAST(? AS name), c.oid, \'SELECT\')
                AND NOT EXISTS (
                    SELECT 1 FROM pg_depend d
                    WHERE d.classid = \'pg_class\'::regclass AND d.objid = c.oid AND d.objsubid = 0 AND d.deptype = \'e\'
                )
            ORDER BY c.relname',
            [$roleName],
        );

        return array_map(static fn (mixed $tableName): string => (string)$tableName, $tableNames);
    }

    /**
     * Default privileges give the role access to the tables created in the public schema in the future,
     * both with the IN SCHEMA public clause and without a schema (defaclnamespace = 0 applies to every schema)
     */
    public function hasDefaultPrivilegesOnPublicSchemaTables(string $roleName): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT 1
            FROM pg_default_acl d, aclexplode(d.defaclacl) acl
            WHERE d.defaclobjtype = \'r\'
                AND (d.defaclnamespace = 0 OR d.defaclnamespace = \'public\'::regnamespace)
                AND acl.grantee = (SELECT r.oid FROM pg_roles r WHERE r.rolname = ?)',
            [$roleName],
        );
    }
}
