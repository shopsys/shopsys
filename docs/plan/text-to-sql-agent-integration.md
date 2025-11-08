# Text-to-SQL Agent Integration Plan

## Overview

Add a Mastra-powered text-to-SQL agent to the Shopsys admin that allows administrators to query the Shopsys database using natural language. The agent will generate SQL queries, present them for review, and execute them safely with proper security constraints.

## Current State Analysis

**Existing Infrastructure:**
- Working Mastra service with weather agent (commit ca44861574)
- Nginx reverse proxy configured (`/mastra/api/*`)
- Admin controller, template, and menu integration patterns established
- Session-based thread/resource management for conversation continuity
- PostgreSQL database with multi-domain, multi-language e-commerce schema

**Text-to-SQL Example:**
- Located at `/Users/neon/shopsys/mastra_01/text-to-sql`
- Provides introspection, generation, and execution tools
- Uses workflow with suspend/resume for human approval
- Configured for generic PostgreSQL databases

## Desired End State

**Functionality:**
- Natural language queries about products, orders, customers, categories, etc.
- Generated SQL displayed for admin review before execution
- Cross-domain query support (admin can query any domain)
- Max 500 rows per query result
- Whitelisted safe tables only (excludes passwords, tokens, sensitive data)
- Chat interface matching weather agent UX pattern

**Verification:**
- Admin can ask "Show me top 10 products by sales" and get results
- SQL queries are displayed before execution with approve/modify options
- Sensitive tables are blocked from queries
- Multi-turn conversations maintain context
- Results are formatted in readable table format

## What We're NOT Doing

- Database seeding functionality (database already populated)
- Write operations (INSERT/UPDATE/DELETE) - read-only queries only
- Direct access to sensitive tables (admin passwords, payment tokens)
- Automatic query execution without review
- Export to CSV/Excel (future enhancement)
- Query history/saved queries (future enhancement)

## Implementation Approach

Adapt the text-to-SQL example to Shopsys by:
1. Creating specialized tools for Shopsys schema introspection and safe SQL execution
2. Building an agent that generates queries for whitelisted tables only
3. Implementing a review-before-execution UI in admin template
4. Reusing existing controller/menu patterns from weather agent
5. Pre-configuring database connection from Symfony parameters

---

## Phase 1: Create SQL Tools for Shopsys

### Overview
Create three tools adapted from the text-to-SQL example: schema introspection, SQL generation, and safe SQL execution with Shopsys-specific security constraints.

### Changes Required

#### 1. Database Introspection Tool
**File**: `mastra-service/src/mastra/tools/database-introspection-tool.ts`

**Purpose**: Analyze Shopsys database schema for whitelisted tables only

**Implementation Details:**
- Filter tables to whitelist (products, categories, orders, customers, brands, etc.)
- Exclude sensitive tables (administrators.password, payment_transactions, *_token_chain)
- Include domain-specific table relationships
- Handle translation table patterns

**Whitelisted Tables (Recommended Safe Set):**
```typescript
const WHITELISTED_TABLES = [
  'products', 'product_translations', 'product_domains', 'product_visibilities',
  'product_parameter_values', 'categories', 'category_translations', 'category_domains',
  'brands', 'brand_translations', 'units', 'unit_translations',
  'parameters', 'parameter_translations', 'parameter_values',
  'orders', 'order_items', 'order_statuses',
  'customers', 'customer_users', // Exclude password field in column filtering
  'billing_addresses', 'delivery_addresses',
  'payments', 'payment_translations', 'transports', 'transport_translations',
  'pricing_groups', 'vats', 'currencies',
  'stores', 'store_opening_hours',
  'blog_articles', 'blog_article_translations', 'blog_categories', 'blog_category_translations',
  'flags', 'product_accessories', 'carts', 'cart_items'
];

// Sensitive columns to exclude even from whitelisted tables
const EXCLUDED_COLUMNS = {
  'customer_users': ['password', 'reset_password_hash'],
  'administrators': ['password', 'login_token']
};
```

**Key Features:**
- Query only whitelisted tables from information_schema
- Filter out sensitive columns from column metadata
- Retrieve foreign key relationships
- Get indexes for query optimization
- Calculate row counts for each table

**Steps:**
- [ ] Create file `mastra-service/src/mastra/tools/database-introspection-tool.ts`
- [ ] Import required dependencies: `createTool`, `Client` from pg, `z` from zod
- [ ] Define `WHITELISTED_TABLES` constant with recommended safe set
- [ ] Define `EXCLUDED_COLUMNS` constant for sensitive fields
- [ ] Implement `execute` function with PostgreSQL client connection
- [ ] Query `pg_tables` filtered by whitelist
- [ ] Query `information_schema.columns` filtered by whitelist and excluded columns
- [ ] Query `information_schema.table_constraints` for foreign keys
- [ ] Query `pg_indexes` for index information
- [ ] Execute `COUNT(*)` queries for each whitelisted table
- [ ] Return structured schema metadata

#### 2. SQL Generation Tool
**File**: `mastra-service/src/mastra/tools/sql-generation-tool.ts`

**Purpose**: Generate SELECT queries using OpenAI with Shopsys-specific context

**Key Adaptations:**
- System prompt includes Shopsys multi-domain patterns
- Mention translation table joins (locale field)
- Enforce 500 row limit
- Include domain_id filtering guidance
- PostgreSQL-specific syntax (ILIKE for case-insensitive search)

**System Prompt Rules:**
```
CRITICAL RULES:
1. ONLY generate SELECT queries (no INSERT, UPDATE, DELETE, DROP, CREATE, ALTER)
2. ALWAYS use proper table and column qualification (table_name.column_name)
3. ALWAYS add LIMIT 500 to prevent large result sets
4. Use ILIKE for case-insensitive text searches in PostgreSQL
5. Return valid PostgreSQL syntax only

SHOPSYS SCHEMA PATTERNS:
- Multi-domain: Most entities have *_domains tables with domain_id
- Translations: Most entities have *_translations tables with locale ('en', 'cs', 'sk')
- Domain-specific data: product_domains, category_domains (SEO, descriptions, VAT)
- Soft deletes: Check for deleted = FALSE where applicable
- Nested categories: Use lft/rgt for hierarchical queries
- Product visibility: Use product_visibilities table for proper filtering
```

**Steps:**
- [ ] Create file `mastra-service/src/mastra/tools/sql-generation-tool.ts`
- [ ] Import dependencies: `createTool`, `openai`, `generateObject`, `z`
- [ ] Define Zod schema for SQL output (sql, explanation, confidence, assumptions, tablesUsed)
- [ ] Create comprehensive system prompt with Shopsys patterns
- [ ] Implement `createSchemaDescription()` helper function
- [ ] Implement `execute` function using OpenAI `gpt-4o` model
- [ ] Set temperature to 0.1 for deterministic SQL generation
- [ ] Return structured SQL with explanation and metadata

#### 3. SQL Execution Tool
**File**: `mastra-service/src/mastra/tools/sql-execution-tool.ts`

**Purpose**: Execute SELECT queries safely with validation

**Security Validations:**
- Enforce SELECT-only queries
- Add 500 row limit if not present
- Validate table whitelist
- Block forbidden keywords (DROP, DELETE, INSERT, UPDATE, etc.)
- Format results for UI display

**Steps:**
- [ ] Create file `mastra-service/src/mastra/tools/sql-execution-tool.ts`
- [ ] Import dependencies and define `WHITELISTED_TABLES` constant
- [ ] Define output schema with success, data, rowCount, executedQuery, error fields
- [ ] Implement security validation: check query starts with SELECT
- [ ] Validate no forbidden keywords (DROP, DELETE, INSERT, UPDATE, ALTER, CREATE, TRUNCATE, GRANT, REVOKE)
- [ ] Ensure LIMIT clause present (add LIMIT 500 if missing)
- [ ] Cap LIMIT to max 500 rows if higher value specified
- [ ] Execute query via PostgreSQL client
- [ ] Return structured result with data array and metadata
- [ ] Handle errors gracefully with descriptive messages

### Success Criteria

#### Automated Verification:
- [ ] Tools compile without TypeScript errors: `docker compose exec mastra pnpm run build`
- [ ] Tools registered in Mastra instance
- [ ] Introspection tool returns only whitelisted tables
- [ ] Generation tool produces valid SELECT queries
- [ ] Execution tool blocks non-SELECT queries

#### Manual Verification:
- [ ] Introspection excludes sensitive columns (passwords, tokens)
- [ ] Generated queries include proper JOINs for translations/domains
- [ ] Execution enforces 500 row limit
- [ ] Error messages are clear and actionable

---

## Phase 2: Create SQL Agent with Memory

### Overview
Create a conversational agent that uses the SQL tools to answer natural language questions about the Shopsys database.

### Changes Required

#### 1. SQL Agent Definition
**File**: `mastra-service/src/mastra/agents/sql-agent.ts`

**Purpose**: Conversational agent for database queries with instructions tailored to Shopsys

**Agent Configuration:**
- Name: "Shopsys SQL Assistant"
- Model: `openai/gpt-4o-mini`
- Tools: databaseIntrospectionTool, sqlGenerationTool, sqlExecutionTool
- Memory: LibSQLStore (shared with weather agent)

**System Instructions:**
```
YOUR ROLE:
- Help administrators query the Shopsys database using natural language
- Generate accurate SQL queries that respect the platform's multi-domain architecture
- Explain query results in business terms
- Suggest follow-up questions and insights

WORKFLOW:
1. When user asks a question, first introspect the database schema if needed
2. Generate a SELECT query using the sql-generation tool
3. IMPORTANT: Present the generated SQL to the user for review
4. Wait for user approval before executing
5. Execute the approved query (or modified version) using sql-execution tool
6. Format and explain the results clearly
```

**Steps:**
- [ ] Create file `mastra-service/src/mastra/agents/sql-agent.ts`
- [ ] Import Agent, Memory, LibSQLStore, and all three SQL tools
- [ ] Define agent with comprehensive system instructions
- [ ] Configure model as `openai/gpt-4o-mini`
- [ ] Attach all three tools to agent
- [ ] Configure Memory with LibSQLStore (`file:../mastra.db`)
- [ ] Add Shopsys-specific context to instructions (multi-domain, translations, soft deletes)
- [ ] Emphasize review-before-execute workflow
- [ ] Include example interaction in instructions
- [ ] Export agent for registration

#### 2. Register Agent in Mastra Instance
**File**: `mastra-service/src/mastra/index.ts`

**Changes**: Add sqlAgent to registered agents

**Steps:**
- [ ] Import `sqlAgent` from './agents/sql-agent'
- [ ] Add `sqlAgent` to `agents` object in Mastra configuration
- [ ] Verify agent accessible via `mastra.getAgent('sqlAgent')`

### Success Criteria

#### Automated Verification:
- [ ] Agent compiles without TypeScript errors
- [ ] Agent registered in Mastra instance: `mastra.getAgent('sqlAgent')` succeeds
- [ ] Tools accessible via agent: `sqlAgent.getTools()` returns 3 tools
- [ ] Memory configured: agent can store conversation history

#### Manual Verification:
- [ ] Agent responds to "What tables are available?" by listing whitelisted tables
- [ ] Agent generates SQL and waits for approval (doesn't auto-execute)
- [ ] Agent explains queries in business terms
- [ ] Conversation context maintained across multiple questions

---

## Phase 3: Admin Controller and Template with Review UI

### Overview
Create admin controller action and template for the SQL agent with a review-before-execution interface.

### Changes Required

#### 1. Add SQL Dashboard Action to Controller
**File**: `packages/framework/src/Controller/Admin/MastraController.php`

**Changes**: Add new action for SQL chat (reuse existing thread/resource logic)

**New Methods:**
- `sqlDashboardAction()` - Main SQL chat page
- `newSqlConversationAction()` - Clear SQL thread, start new conversation
- `getOrCreateSqlThreadId()` - Session management for SQL agent

**New Constants:**
- `SESSION_SQL_THREAD_KEY = 'mastra_sql_thread_id'` - Separate thread from weather agent

**Steps:**
- [ ] Add constant `SESSION_SQL_THREAD_KEY` for SQL agent thread storage
- [ ] Implement `sqlDashboardAction()` method with route `/superadmin/mastra/sql`
- [ ] Implement `newSqlConversationAction()` method with route `/superadmin/mastra/sql/new-conversation`
- [ ] Implement `getOrCreateSqlThreadId()` method (similar to weather thread logic)
- [ ] Generate thread ID format: `sql_thread_{userId}_{randomHex}`
- [ ] Reuse existing `getResourceId()` method (same resource for both agents)
- [ ] Pass `threadId` and `resourceId` to SQL template
- [ ] Add `#[SuperAdminOnly]` security attribute (inherited from class)

#### 2. Create SQL Chat Template
**File**: `packages/administration/templates/content/mastra/sql.html.twig`

**Purpose**: Chat interface with SQL preview and approval workflow

**Template Structure:**
- Extends `@ShopsysAdministration/layout/layout_with_panel.html.twig`
- Title blocks: "SQL Assistant" with "Integrations" breadcrumb
- Chat container with scrollable message list
- Welcome message with example queries
- Input form for natural language questions
- JavaScript for conversation management

**UI Features:**
- Display threadId and resourceId in header (debugging)
- "New Conversation" button
- Message bubbles (user vs assistant styling)
- SQL preview with syntax highlighting
- Approval buttons (Execute / Cancel)
- Results table with formatted output
- Loading states
- Error handling

**Message Types:**
- `message-user` - User questions (blue, right-aligned)
- `message-assistant` - Agent responses (white, left-aligned)
- `message-sql-preview` - SQL query for review (yellow, with buttons)
- `message-loading` - Processing indicator (gray, italic)
- `message-error` - Error messages (red)

**JavaScript Functions:**
- `sendMessage()` - Send user question to agent
- `executeApprovedQuery()` - Execute SQL after approval
- `formatSqlPreview()` - Create SQL preview with approval buttons
- `formatResults()` - Create HTML table from query results
- `scrollToBottom()` - Auto-scroll to latest message

**Steps:**
- [ ] Create file `packages/administration/templates/content/mastra/sql.html.twig`
- [ ] Extend standard admin layout with panel
- [ ] Add title blocks with translations
- [ ] Create chat container div with max-height and scroll
- [ ] Add welcome message with example queries
- [ ] Create input form with label and submit button
- [ ] Add CSS styles for all message types
- [ ] Implement JavaScript state management (threadId, resourceId, conversationHistory)
- [ ] Implement `sendMessage()` function to call `/mastra/api/agents/sqlAgent/generate`
- [ ] Implement SQL detection logic (check for "SELECT" in response)
- [ ] Implement `formatSqlPreview()` to create approval UI
- [ ] Implement `executeApprovedQuery()` to send execution request
- [ ] Implement `formatResults()` to create HTML table from JSON data
- [ ] Add event listeners for form submit and enter key
- [ ] Add auto-scroll on new messages
- [ ] Handle toolResults from agent response for execution output

### Success Criteria

#### Automated Verification:
- [ ] Controller routes compile: `docker compose exec php-fpm php bin/console debug:router | grep mastra_sql`
- [ ] Template renders without Twig errors
- [ ] JavaScript passes syntax validation

#### Manual Verification:
- [ ] SQL dashboard accessible at `/admin/superadmin/mastra/sql`
- [ ] SQL query displayed in code block for review
- [ ] "Execute Query" and "Cancel" buttons functional
- [ ] Results formatted as readable table
- [ ] Session persistence across page refreshes
- [ ] "New Conversation" clears SQL thread

---

## Phase 4: Admin Menu Integration

### Overview
Add "SQL Assistant" menu item under existing "Mastra Assistant" in Integrations menu.

### Changes Required

#### 1. Add Menu Constant
**File**: `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php`

**Location**: Around line 189 with other integration constants

**Steps:**
- [ ] Add constant `MASTRA_SQL_DASHBOARD = 'mastra_sql_dashboard'`

#### 2. Add Menu Item
**File**: `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php`

**Location**: In `createIntegrationsMenu()` method around line 901

**Menu Hierarchy:**
```
Integrations (icon: puzzle)
├── XML Feeds
├── Mastra Assistant (weather agent)
├── SQL Assistant (new)
└── Heureka Settings
```

**Steps:**
- [ ] Locate `createIntegrationsMenu()` method
- [ ] Add menu item after `MASTRA_DASHBOARD`:
  ```php
  $integrationsMenu->addChild(
      static::MASTRA_SQL_DASHBOARD,
      ['route' => 'admin_mastra_sqldashboard', 'label' => t('SQL Assistant')]
  );
  ```
- [ ] Verify route name matches controller route

### Success Criteria

#### Automated Verification:
- [ ] Code compiles: `docker compose exec php-fpm php phing standards-fix`
- [ ] Menu constant defined
- [ ] Route name matches controller route

#### Manual Verification:
- [ ] "SQL Assistant" appears in Integrations menu
- [ ] Menu item links to `/admin/superadmin/mastra/sql`
- [ ] Menu item only visible to super admins

---

## Phase 5: Database Connection Configuration

### Overview
Configure database connection string from Symfony parameters and pass to Mastra tools via environment variables.

### Changes Required

#### 1. Create Database Connection Helper
**File**: `mastra-service/src/lib/db-connection.ts`

**Purpose**: Build PostgreSQL connection string from environment variables

**Steps:**
- [ ] Create directory `mastra-service/src/lib/`
- [ ] Create file `db-connection.ts`
- [ ] Implement `getShopsysConnectionString()` function
- [ ] Read environment variables: DATABASE_HOST, DATABASE_PORT, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD
- [ ] Provide defaults matching Shopsys configuration (postgres, 5432, shopsys, root, root)
- [ ] Return formatted connection string: `postgresql://{user}:{password}@{host}:{port}/{database}`
- [ ] Export function for use in tools

#### 2. Update Docker Compose Configuration
**File**: `docker-compose.yml`

**Changes**: Add database environment variables to mastra service

**Steps:**
- [ ] Locate `mastra` service in docker-compose.yml
- [ ] Add environment variables to `environment` section:
  - `DATABASE_HOST: postgres`
  - `DATABASE_PORT: 5432`
  - `DATABASE_NAME: shopsys`
  - `DATABASE_USER: root`
  - `DATABASE_PASSWORD: root`
- [ ] Verify mastra service depends on postgres service

#### 3. Update Tools to Use Connection Helper
**Files**:
- `mastra-service/src/mastra/tools/database-introspection-tool.ts`
- `mastra-service/src/mastra/tools/sql-generation-tool.ts`
- `mastra-service/src/mastra/tools/sql-execution-tool.ts`

**Changes**: Use connection helper instead of accepting connection string as input

**Steps:**
- [ ] Import `getShopsysConnectionString` in introspection tool
- [ ] Remove `connectionString` from introspection tool inputSchema
- [ ] Call `getShopsysConnectionString()` in introspection tool execute function
- [ ] Import `getShopsysConnectionString` in execution tool
- [ ] Remove `connectionString` from execution tool inputSchema
- [ ] Call `getShopsysConnectionString()` in execution tool execute function
- [ ] Verify generation tool doesn't need connection (only uses schema data)

### Success Criteria

#### Automated Verification:
- [ ] Mastra service starts without connection errors: `docker compose logs mastra`
- [ ] Tools can connect to database
- [ ] TypeScript compiles: `docker compose exec mastra pnpm run build`

#### Manual Verification:
- [ ] Introspection tool returns Shopsys tables
- [ ] Execution tool successfully runs queries
- [ ] No hardcoded passwords in code (only in docker-compose.yml and .env)
- [ ] Connection helper reads from environment variables correctly

---

## Phase 6: Testing and Security Validation

### Overview
Comprehensive testing of security constraints, query generation, and user experience.

### Security Testing

#### Table Whitelist Validation:
- [ ] Verify `administrators` table excluded from introspection
- [ ] Verify `payment_transactions` table excluded from introspection
- [ ] Verify only whitelisted tables returned in schema
- [ ] Test query against non-whitelisted table (should fail or be blocked)

#### Column Filtering Validation:
- [ ] Verify `customer_users.password` excluded from schema
- [ ] Verify `customer_users.reset_password_hash` excluded from schema
- [ ] Verify `administrators.password` excluded if admin table whitelisted
- [ ] Verify `administrators.login_token` excluded if admin table whitelisted

#### Query Type Validation:
- [ ] Test `DELETE FROM products` is blocked
- [ ] Test `UPDATE orders SET status = ...` is blocked
- [ ] Test `INSERT INTO products VALUES (...)` is blocked
- [ ] Test `DROP TABLE products` is blocked
- [ ] Test `ALTER TABLE products ADD COLUMN ...` is blocked
- [ ] Test `TRUNCATE TABLE products` is blocked
- [ ] Verify only SELECT queries allowed

#### Row Limit Enforcement:
- [ ] Verify queries without LIMIT get `LIMIT 500` added automatically
- [ ] Verify `LIMIT 1000` gets reduced to `LIMIT 500`
- [ ] Verify `LIMIT 100` remains unchanged
- [ ] Verify maximum 500 rows returned from any query

### Functional Testing

#### Simple Queries:
- [ ] "How many products are there?" returns correct count
- [ ] "Show me 5 products" returns 5 product rows
- [ ] "List all brands" returns brand names
- [ ] "What categories exist?" returns category list

#### JOIN Queries:
- [ ] "Show products with category names" includes proper translation joins
- [ ] "List products with their brands" joins brands table correctly
- [ ] "Show orders with customer names" joins customer_users correctly
- [ ] Query includes locale filter for translations (e.g., `pt.locale = 'en'`)

#### Multi-Domain Queries:
- [ ] "Show products from domain 1" filters by `domain_id = 1`
- [ ] "Show products from domain 2" filters by `domain_id = 2`
- [ ] "Compare order counts across domains" groups by domain_id
- [ ] Agent can query across all domains without restriction

#### Translation Queries:
- [ ] "List categories in Czech language" uses `locale = 'cs'`
- [ ] "Show products in English" uses `locale = 'en'`
- [ ] "Show products in Slovak" uses `locale = 'sk'`

#### Edge Cases:
- [ ] Query with no matches displays "No results found"
- [ ] Empty table returns 0 rows gracefully
- [ ] Invalid column name shows clear error message
- [ ] Malformed SQL shows clear error message

### UX Testing

#### SQL Preview Flow:
- [ ] Generated SQL displayed in formatted code block
- [ ] SQL code uses monospace font and syntax highlighting background
- [ ] Explanation text displays below SQL
- [ ] Approve button labeled "Execute Query" appears
- [ ] Cancel button appears next to approve button

#### Approval Flow:
- [ ] Click "Execute Query" triggers query execution
- [ ] Loading indicator shows during execution
- [ ] Results appear after execution completes
- [ ] Execution failure shows error message
- [ ] Can approve multiple queries in same conversation

#### Cancel Flow:
- [ ] Click "Cancel" prevents query execution
- [ ] "Query cancelled" message appears
- [ ] Conversation can continue after cancellation
- [ ] Can ask new question after cancellation

#### Results Display:
- [ ] Results shown as HTML table with borders
- [ ] Table headers match column names from query
- [ ] Row count displayed above table
- [ ] NULL values shown as "(null)"
- [ ] Long text values readable (not truncated)
- [ ] Empty results show "No results found" message

#### Conversation Features:
- [ ] Follow-up questions maintain context from previous queries
- [ ] Agent references previous results in responses
- [ ] Thread ID persists in session across page refreshes
- [ ] Resource ID same for all conversations from same admin
- [ ] "New Conversation" button clears thread and starts fresh

#### Session Persistence:
- [ ] Refresh page preserves thread ID
- [ ] Can continue conversation after page refresh
- [ ] Conversation history maintained in session
- [ ] Different browser tab has different thread (new conversation)

### Performance Testing

#### Query Execution:
- [ ] Query returning 100 rows completes in < 2 seconds
- [ ] Query returning 500 rows completes in < 5 seconds
- [ ] Complex JOIN (3+ tables) completes within 10 seconds
- [ ] No timeout errors for normal queries

#### Schema Introspection:
- [ ] First introspection completes within 30 seconds
- [ ] Introspection returns complete schema metadata
- [ ] Row count queries complete without timeout
- [ ] Index queries complete without timeout

#### UI Responsiveness:
- [ ] Message appears immediately after user sends question
- [ ] Loading indicator shows within 100ms
- [ ] Results render within 500ms after receiving response
- [ ] Auto-scroll smooth and immediate

### Manual Test Scenarios

#### Scenario 1: Basic Product Query
```
User: "Show me the top 5 products"
Expected: SQL preview with SELECT ... FROM products ... LIMIT 5
Action: Click "Execute Query"
Expected: Table with 5 products displayed
```

**Test Steps:**
- [ ] Navigate to `/admin/superadmin/mastra/sql`
- [ ] Type "Show me the top 5 products"
- [ ] Verify SQL appears in code block
- [ ] Verify "Execute Query" button visible
- [ ] Click "Execute Query"
- [ ] Verify table with 5 rows appears
- [ ] Verify product names visible in results

#### Scenario 2: Cross-Domain Query
```
User: "How many orders are in domain 1 vs domain 2?"
Expected: SQL with GROUP BY domain_id
Action: Approve and execute
Expected: Table showing counts per domain
```

**Test Steps:**
- [ ] Ask "How many orders are in domain 1 vs domain 2?"
- [ ] Verify SQL includes `GROUP BY domain_id`
- [ ] Click "Execute Query"
- [ ] Verify results show 2 rows (one per domain)
- [ ] Verify each row has domain_id and count

#### Scenario 3: Security Block
```
User: "Show me administrator passwords"
Expected: Agent explains this table is not available OR query fails with security error
```

**Test Steps:**
- [ ] Ask "Show me administrator passwords"
- [ ] Verify error message or agent explanation
- [ ] Verify no password data displayed
- [ ] Verify query not executed

#### Scenario 4: Conversation Continuity
```
User: "Show top 5 categories"
[Results displayed]
User: "Now show me products in the first category"
Expected: Agent references previous query results, generates new query
```

**Test Steps:**
- [ ] Ask "Show top 5 categories"
- [ ] Execute query and verify 5 categories shown
- [ ] Ask "Now show me products in the first category"
- [ ] Verify agent understands context
- [ ] Verify new SQL generated referencing category from previous result
- [ ] Execute and verify products from that category

#### Scenario 5: Translation Query
```
User: "List product names in Czech language"
Expected: SQL with JOIN product_translations WHERE locale = 'cs'
```

**Test Steps:**
- [ ] Ask "List product names in Czech language"
- [ ] Verify SQL includes `JOIN product_translations`
- [ ] Verify SQL includes `WHERE locale = 'cs'` or `AND pt.locale = 'cs'`
- [ ] Execute query
- [ ] Verify Czech product names in results (if test data exists)

### Success Criteria

#### Automated Verification:
- [ ] All Phing checks pass: `docker compose exec php-fpm php phing standards-fix phpstan`
- [ ] Mastra service builds: `docker compose exec mastra pnpm run build`
- [ ] No console errors in browser DevTools
- [ ] No TypeScript compilation errors

#### Manual Verification:
- [ ] All security tests pass (18/18 tests)
- [ ] All functional tests pass (15/15 tests)
- [ ] All UX tests pass (20/20 tests)
- [ ] All performance tests pass (8/8 tests)
- [ ] All 5 test scenarios complete successfully

---

## Related Documentation

- Existing Mastra integration: `docs/mastra-integration-plan.md`
- Text-to-SQL example: `/Users/neon/shopsys/mastra_01/text-to-sql`
- Admin controller patterns: `packages/framework/src/Controller/Admin/MastraController.php`
- Admin navigation: `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php`

## Configuration Summary

**Database Connection:**
- Host: postgres (Docker container name)
- Port: 5432
- Database: shopsys
- User: root
- Password: root
- Connection managed via environment variables

**Query Constraints:**
- Query type: SELECT only
- Row limit: 500 rows maximum
- Table access: Whitelisted tables only (see Phase 1)
- Column filtering: Sensitive columns excluded (passwords, tokens)
- Domain filtering: Cross-domain queries allowed (agent decides)

**User Experience:**
- Review-before-execute workflow (not auto-execute)
- SQL preview with approve/cancel buttons
- Results as formatted HTML table
- Conversation memory and context
- Session-based thread persistence

## Notes

- **Connection String Security**: Database credentials passed via environment variables, never hardcoded in source code
- **Agent Behavior**: Agent instructions emphasize review-before-execute workflow to ensure admin has control
- **Table Whitelist**: Easily extensible - add tables to `WHITELISTED_TABLES` array in both introspection and execution tools
- **Row Limit**: Enforced at tool level (500 rows) to prevent performance issues and browser memory overload
- **Multi-Agent Support**: Weather and SQL agents share Mastra instance but have separate thread IDs for independent conversations
- **Separate Sessions**: SQL agent uses `mastra_sql_thread_id` session key, weather uses `mastra_thread_id`, preventing cross-contamination
- **Shared Resources**: Both agents use same `resourceId` (admin user identifier) for resource-scoped memory
- **Future Enhancements**: Export results to CSV, saved queries, query history, visual query builder, query templates
