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

Validation uses the [`ext-pg_query`](https://flow-php.com/documentation/components/extensions/pg-query-ext/) PHP extension built on [`libpg_query`](https://github.com/pganalyze/libpg_query), so SQL is parsed into a PostgreSQL AST and validated structurally.

In practice, this means:

- only read-only queries are allowed
- only the exposed schema can be queried

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

- In production, `MCP_DATABASE_USER` and `MCP_DATABASE_PASSWORD` must point to a dedicated read-only PostgreSQL user, not to the main application database user.
- MCP access tokens are stored hashed in the database.
- Both manual and OAuth-issued tokens expire.
- Query limits and statement timeout are configured through bundle configuration.
- Public MCP entrypoints are rate-limited as baseline abuse protection. Dynamic client registration and token exchange are limited by client IP. MCP runtime requests always consume an IP-based limit and also consume a token-public-ID limit when the bearer token has the generated token shape.
- MCP requests are logged to the dedicated `mcp` Monolog channel.
