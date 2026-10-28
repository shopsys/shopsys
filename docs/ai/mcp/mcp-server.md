# MCP server

Shopsys Platform includes a production MCP server for external AI clients such as Claude Code or Codex CLI.

In practice, this gives a project team a controlled way to let an AI client read selected project data.

[TOC]

## What the MCP server means in practice

For administrators, it means:

- an AI client can connect to the project over MCP
- it can inspect only the schema that the application explicitly exposes
- it can run validated read-only SQL over that exposed schema
- access can be granted either through browser-based OAuth or through manually generated tokens

For developers, it means:

- MCP exposure must be configured explicitly in code
- the AI client never gets automatic access to the whole database
- SQL access is intentionally limited to a validated read-only subset

The MCP server does not:

- it does not expose the whole database automatically
- it does not allow write queries
- it does not include any built-in AI orchestration or agent workflow

## What an AI client can do

The MCP server exposes three tools:

1. `getDatabaseTableNames`
   Returns the list of database tables that are intentionally exposed to MCP clients.
2. `getDatabaseSchema`
   Returns the exposed schema for selected tables, including columns, primary keys, and foreign keys.
3. `executeSql`
   Executes a validated read-only SQL query against the exposed schema.

For an end user, this means the AI client can answer questions about the project’s data model and query selected business data, but only within the boundaries explicitly allowed by the application.

## How users authenticate

The MCP server supports two authentication paths.

### Manual token

This is the fallback path for clients that do not support browser-based OAuth login.

An administrator generates the token in Shopsys administration and then configures the MCP client with that token manually.

### Browser-based client authorization

This is the preferred path for clients that support OAuth-based login.

The MCP client starts the OAuth flow, the administrator confirms access in Shopsys administration, and the client then receives an MCP bearer token automatically.

The detailed OAuth flow is described in [MCP browser authentication flow](./mcp-browser-auth-flow.md).

## Administration page

The MCP administration page is available under:

- `Settings > Superadmin > MCP server`

It is restricted to superadministrators and provides the MCP token overview and client setup guidance.

## Runtime configuration

The MCP server is enabled only when both environment variables are configured:

- `MCP_DATABASE_USER`
- `MCP_DATABASE_PASSWORD`

If either value is missing or empty, MCP runtime features are disabled. In that state, MCP administration links are hidden, direct access to MCP administration URLs returns HTTP 404, OAuth/runtime entrypoints are not available, and MCP schema generation is skipped.

For local development and for CI builds, Shopsys Platform provides default values in `.env`, and the application prepares the MCP database role itself when it creates the database schema. For production deployments, set both variables explicitly and create the role yourself (it requires a database superuser, so you may need to ask your hosting or devops to prepare it). We strongly recommend using a dedicated PostgreSQL user for the MCP connection.

The role must exist before the database migrations run, because they grant it the access to the views in the `mcp` schema (see [How SQL execution stays bounded](#how-sql-execution-stays-bounded)).
The dedicated user needs only to log in, connect to the database, and look up objects in the `public` schema:

```sql
CREATE ROLE "<mcp-user>" WITH LOGIN PASSWORD '<password>' CONNECTION LIMIT 10 NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION NOBYPASSRLS;
GRANT CONNECT ON DATABASE "<database>" TO "<mcp-user>";
GRANT USAGE ON SCHEMA public TO "<mcp-user>";
ALTER ROLE "<mcp-user>" SET search_path = mcp;
ALTER ROLE "<mcp-user>" SET default_transaction_read_only = on;
```

The MCP connection sets the search path and the read-only mode itself, the role defaults keep them in place when a connection pooler resets the session.
The `USAGE` privilege is needed because PostgreSQL inlines SQL functions used in expression indexes (for example `normalized()`) with the privileges of the querying role, and the migrations fail when the role does not have it.
Do not grant the user any privileges on the tables in the `public` schema. The SQL validator rejects references outside the `mcp` schema, and without such privileges the database itself keeps MCP queries away from the tables as well.
For the same reason, avoid `SECURITY DEFINER` functions in the `public` schema, the role could call them with the privileges of their owner.

The statement timeout of the MCP connection limits how long a query runs, but not how much memory it uses.
The MCP connection therefore also sets `work_mem` for every session (`query.work_mem_kb` in the bundle configuration, `4MB` by default, which is the default of PostgreSQL), so the memory of sorts and hashes stays low even when the server raises it globally.
The size of temporary files can be limited only for the role, and setting `temp_file_limit` requires a superuser. We recommend setting both for the role:

```sql
ALTER ROLE "<mcp-user>" SET work_mem = '4MB';
ALTER ROLE "<mcp-user>" SET temp_file_limit = '256MB';
```

## What developers need to configure

MCP exposure is explicit. Nothing is exposed automatically just because it exists as a Doctrine entity.

The expected setup is simple:

1. decide whether an entity should be exposed at all
2. mark the entity with `#[AsMcpTable(exposed: ...)]`
3. if the entity is exposed, mark each relevant mapped field or association with `#[AsMcpColumn(exposed: ...)]`

In other words:

- `AsMcpTable` is the table-level decision
- `AsMcpColumn` is the field-level decision inside that table

They are complementary, not alternatives. `AsMcpColumn` does not replace `AsMcpTable`. An entity must first be handled at table level, and then its individual fields must be handled at column level.

### Table exposure

Expose an entity as an MCP table:

```php
#[AsMcpTable(exposed: true)]
class Product
{
}
```

Explicitly declare that an entity must not be exposed:

```php
#[AsMcpTable(exposed: false)]
class AdministratorMcpToken
{
}
```

`false` means: this entity was reviewed deliberately and must not be exposed.

### Column exposure

Expose a mapped property:

```php
#[AsMcpColumn(exposed: true)]
protected $name;
```

Explicitly declare that a mapped property must not be exposed:

```php
#[AsMcpColumn(exposed: false)]
protected $secretValue;
```

Again, `false` means explicit opt-out after review.

For inherited fields that are mapped on a parent class and cannot be annotated directly on the local property, use `AsMcpInheritedColumn` on the entity class:

```php
#[AsMcpInheritedColumn(fieldName: 'id', exposed: true)]
#[AsMcpInheritedColumn(fieldName: 'locale', exposed: true)]
class ProductTranslation extends AbstractTranslation
{
}
```

This is useful when the mapped property comes from a parent class that cannot be modified directly, for example translation entities extending `Prezent\Doctrine\Translatable\Entity\AbstractTranslation`.

### Are the attributes mandatory?

Yes, for entities covered by the MCP exposure rule, the intent must be explicit.

That means the code should clearly say:

- this entity is exposed or not exposed
- this field is exposed or not exposed

The goal is to avoid accidental exposure and to make the reviewed MCP surface obvious in code review.

## How this is enforced

The explicit configuration described above is enforced automatically by the custom PHPStan rule `McpEntityExposureAttributeRule`.

The rule checks ORM entities in the `App\\` and `Shopsys\\` namespaces and requires:

- `#[AsMcpTable(exposed: bool)]` on every checked Doctrine entity
- `#[AsMcpColumn(exposed: bool)]` on every mapped scalar field, embedded field, and relevant owning-side association of an exposed entity
- `#[AsMcpInheritedColumn(fieldName: '...', exposed: bool)]` for inherited mapped fields that need explicit MCP exposure on the entity class

## How SQL execution stays bounded

The `executeSql` tool does not run arbitrary SQL blindly.

MCP queries run against database views in a dedicated `mcp` schema. There is one view per exposed table, and it contains only the exposed columns, so hidden columns do not exist for MCP queries at all.
The MCP connection uses `search_path = mcp`, so unqualified table names in a query resolve to these views.

The views are recreated by `shopsys:migrations:migrate` in the migration transaction (before the migrations they are dropped, after the migrations and the schema check they are created again), so they always follow the current exposure attributes and database schema.
The `mcp` schema is owned by the application, it is dropped with everything in it on every migration (even when MCP is disabled), so do not create any other objects in it.
When your database already uses a schema named `mcp` for something else, choose another name for the views schema (and use it in the `search_path` of the MCP role as well):

```yaml
# config/packages/shopsys_mcp.yaml
shopsys_mcp:
    database:
        views_schema_name: mcp_views
```

While the migrations run, MCP queries that need the views wait for the lock timeout of the MCP connection and then fail with a message that the system is temporarily unavailable.

The views reflect the exposure attributes only after they are recreated, so recreate them manually when the attributes change without running migrations (for example during local development), or after importing a database dump, which does not contain the `mcp` schema:

```bash
php bin/console shopsys:mcp:database-views:recreate
```

On top of that, validation uses the [`ext-pg_query`](https://flow-php.com/documentation/components/extensions/pg-query-ext/) PHP extension built on [`libpg_query`](https://github.com/pganalyze/libpg_query), so SQL is parsed into a PostgreSQL AST and validated structurally.

In practice, this means:

- only a single read-only `SELECT` with a top-level `LIMIT` is allowed
- only allowlisted SQL constructs (joins, subqueries, CTEs, `CASE`, operators, grouping, window definitions and similar) can be used, anything else is rejected, including syntax added in future PostgreSQL versions
- only allowlisted functions and types (for example in type casts) can be used, the functions also in SQL standard syntax like `EXTRACT(YEAR FROM ...)`; the only exception is the `alias.function` notation, which PostgreSQL reads as a call of a built-in function with the whole row of a view when the view has no such column
- names of tables, columns, operators and collations must not be qualified with a schema or database, so a query cannot leave the `mcp` schema
- only exposed tables can be queried

The exposed schema used for validation is generated during cache warmup and stored as a JSON artifact at:

- `%kernel.cache_dir%/mcp-schema.json`

The artifact is used by:

- `getDatabaseTableNames`
- `getDatabaseSchema`
- SQL validation for `executeSql`

It can be also generated manually by running:

```bash
php bin/console shopsys:mcp:generate-schema
```

## Operational notes

- MCP access tokens are stored hashed in the database.
- Both manual and OAuth-issued tokens expire.
- Query limits and statement timeout are configured through bundle configuration.
- Public MCP entrypoints are rate-limited as baseline abuse protection. Dynamic client registration and token exchange are limited by client IP. MCP runtime requests always consume an IP-based limit and also consume a token-public-ID limit when the bearer token has the generated token shape.
- MCP requests are logged to the dedicated `mcp` Monolog channel.
