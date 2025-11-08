# Text-to-SQL Agent Integration — Implementation Plan

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
- Write operations (INSERT/UPDATE/DELETE) - read-only queries
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

**Key Adaptations:**
- Filter tables to whitelist (products, categories, orders, customers, brands, etc.)
- Exclude sensitive tables (administrators.password, payment_transactions, *_token_chain)
- Include domain-specific table relationships
- Handle translation table patterns

```typescript
import { createTool } from '@mastra/core/tools';
import { Client } from 'pg';
import { z } from 'zod';

// Whitelisted tables based on user selection: "Recommended safe set"
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
const EXCLUDED_COLUMNS: Record<string, string[]> = {
  'customer_users': ['password', 'reset_password_hash'],
  'administrators': ['password', 'login_token']
};

export const databaseIntrospectionTool = createTool({
  id: 'database-introspection',
  description: 'Analyzes Shopsys database schema for whitelisted tables',
  inputSchema: z.object({
    connectionString: z.string().describe('PostgreSQL connection string')
  }),
  outputSchema: z.object({
    tables: z.array(z.any()),
    columns: z.array(z.any()),
    relationships: z.array(z.any()),
    indexes: z.array(z.any()),
    rowCounts: z.record(z.number()),
  }),
  execute: async ({ context }) => {
    const client = new Client({
      connectionString: context.connectionString,
      connectionTimeoutMillis: 30000,
      statement_timeout: 60000,
      query_timeout: 60000,
    });

    try {
      await client.connect();

      // Query only whitelisted tables
      const tablesQuery = `
        SELECT tablename as name, schemaname as schema
        FROM pg_tables
        WHERE schemaname = 'public'
          AND tablename = ANY($1)
        ORDER BY tablename;
      `;
      const tablesResult = await client.query(tablesQuery, [WHITELISTED_TABLES]);

      // Get columns for whitelisted tables, excluding sensitive columns
      const columnsQuery = `
        SELECT
          c.table_name,
          c.column_name,
          c.data_type,
          c.is_nullable,
          c.column_default,
          tc.constraint_type
        FROM information_schema.columns c
        LEFT JOIN information_schema.key_column_usage kcu
          ON c.table_name = kcu.table_name AND c.column_name = kcu.column_name
        LEFT JOIN information_schema.table_constraints tc
          ON kcu.constraint_name = tc.constraint_name
        WHERE c.table_schema = 'public'
          AND c.table_name = ANY($1)
        ORDER BY c.table_name, c.ordinal_position;
      `;
      const columnsResult = await client.query(columnsQuery, [WHITELISTED_TABLES]);

      // Filter out sensitive columns
      const filteredColumns = columnsResult.rows.filter(col => {
        const excludedForTable = EXCLUDED_COLUMNS[col.table_name] || [];
        return !excludedForTable.includes(col.column_name);
      });

      // Get relationships (foreign keys) for whitelisted tables
      const relationshipsQuery = `
        SELECT
          tc.table_name AS from_table,
          kcu.column_name AS from_column,
          ccu.table_name AS to_table,
          ccu.column_name AS to_column,
          tc.constraint_name
        FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu
          ON tc.constraint_name = kcu.constraint_name
        JOIN information_schema.constraint_column_usage ccu
          ON ccu.constraint_name = tc.constraint_name
        WHERE tc.constraint_type = 'FOREIGN KEY'
          AND tc.table_name = ANY($1)
        ORDER BY tc.table_name, kcu.column_name;
      `;
      const relationshipsResult = await client.query(relationshipsQuery, [WHITELISTED_TABLES]);

      // Get indexes for whitelisted tables
      const indexesQuery = `
        SELECT
          tablename AS table_name,
          indexname AS index_name,
          indexdef AS definition
        FROM pg_indexes
        WHERE schemaname = 'public'
          AND tablename = ANY($1)
        ORDER BY tablename, indexname;
      `;
      const indexesResult = await client.query(indexesQuery, [WHITELISTED_TABLES]);

      // Get row counts for whitelisted tables
      const rowCounts: Record<string, number> = {};
      for (const table of tablesResult.rows) {
        const countResult = await client.query(`SELECT COUNT(*) FROM ${table.name}`);
        rowCounts[table.name] = parseInt(countResult.rows[0].count);
      }

      return {
        tables: tablesResult.rows,
        columns: filteredColumns,
        relationships: relationshipsResult.rows,
        indexes: indexesResult.rows,
        rowCounts,
      };
    } finally {
      await client.end();
    }
  }
});
```

#### 2. SQL Generation Tool
**File**: `mastra-service/src/mastra/tools/sql-generation-tool.ts`

**Purpose**: Generate SELECT queries using OpenAI with Shopsys-specific context

**Key Adaptations:**
- System prompt includes Shopsys multi-domain patterns
- Mention translation table joins (locale field)
- Enforce 500 row limit
- Include domain_id filtering guidance
- PostgreSQL-specific syntax (ILIKE for case-insensitive search)

```typescript
import { createTool } from '@mastra/core/tools';
import { openai } from '@ai-sdk/openai';
import { generateObject } from 'ai';
import { z } from 'zod';

const sqlOutputSchema = z.object({
  sql: z.string().describe('Generated SQL query'),
  explanation: z.string().describe('Explanation of what the query does'),
  confidence: z.number().min(0).max(1).describe('Confidence score'),
  assumptions: z.array(z.string()).describe('Assumptions made'),
  tablesUsed: z.array(z.string()).describe('Tables used in query'),
});

export const sqlGenerationTool = createTool({
  id: 'sql-generation',
  description: 'Generates SELECT SQL queries for Shopsys database based on natural language',
  inputSchema: z.object({
    query: z.string().describe('Natural language query'),
    schema: z.any().describe('Database schema metadata')
  }),
  outputSchema: sqlOutputSchema,
  execute: async ({ context }) => {
    const systemPrompt = `You are a PostgreSQL expert specializing in Shopsys e-commerce platform queries.

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

COMMON JOINS:
- Products with translations: JOIN product_translations pt ON pt.translatable_id = p.id AND pt.locale = 'en'
- Products with domains: JOIN product_domains pd ON pd.product_id = p.id AND pd.domain_id = 1
- Categories with translations: JOIN category_translations ct ON ct.translatable_id = c.id AND ct.locale = 'en'

PERFORMANCE:
- Use indexed columns in WHERE: id, domain_id, catnum, email, lft, rgt
- Prefer product_visibilities over complex product filtering
- Always include LIMIT clause (max 500 rows)

RESPONSE FORMAT:
Analyze the request, generate the SQL, explain your reasoning, and rate your confidence.

Available schema:
${createSchemaDescription(context.schema)}`;

    const { object } = await generateObject({
      model: openai('gpt-4o'),
      schema: sqlOutputSchema,
      prompt: context.query,
      system: systemPrompt,
      temperature: 0.1, // Deterministic for SQL generation
    });

    return object;
  }
});

function createSchemaDescription(schema: any): string {
  const { tables, columns, relationships, rowCounts } = schema;

  let description = 'TABLES:\n';
  for (const table of tables) {
    description += `\n${table.name} (${rowCounts[table.name] || 0} rows)\n`;

    const tableCols = columns.filter((c: any) => c.table_name === table.name);
    description += '  Columns:\n';
    for (const col of tableCols) {
      description += `    - ${col.column_name} (${col.data_type})`;
      if (col.constraint_type === 'PRIMARY KEY') description += ' [PK]';
      if (col.is_nullable === 'NO') description += ' [NOT NULL]';
      description += '\n';
    }

    const tableRels = relationships.filter((r: any) => r.from_table === table.name);
    if (tableRels.length > 0) {
      description += '  Foreign Keys:\n';
      for (const rel of tableRels) {
        description += `    - ${rel.from_column} → ${rel.to_table}.${rel.to_column}\n`;
      }
    }
  }

  return description;
}
```

#### 3. SQL Execution Tool
**File**: `mastra-service/src/mastra/tools/sql-execution-tool.ts`

**Purpose**: Execute SELECT queries safely with validation

**Key Adaptations:**
- Enforce SELECT-only queries
- Add 500 row limit if not present
- Validate table whitelist
- Format results for UI display

```typescript
import { createTool } from '@mastra/core/tools';
import { Client } from 'pg';
import { z } from 'zod';

const WHITELISTED_TABLES = [
  'products', 'product_translations', 'product_domains', 'categories', 'category_translations',
  'brands', 'orders', 'order_items', 'customers', 'customer_users', 'payments', 'transports',
  'pricing_groups', 'vats', 'currencies', 'stores', 'blog_articles', 'blog_categories',
  // ... (same list as introspection tool)
];

export const sqlExecutionTool = createTool({
  id: 'sql-execution',
  description: 'Executes SELECT queries on Shopsys database with safety validation',
  inputSchema: z.object({
    connectionString: z.string().describe('PostgreSQL connection string'),
    sql: z.string().describe('SQL query to execute')
  }),
  outputSchema: z.object({
    success: z.boolean(),
    data: z.array(z.any()).optional(),
    rowCount: z.number().optional(),
    executedQuery: z.string(),
    error: z.string().optional(),
  }),
  execute: async ({ context }) => {
    const client = new Client({
      connectionString: context.connectionString,
      connectionTimeoutMillis: 30000,
      statement_timeout: 60000,
      query_timeout: 60000,
    });

    try {
      // Security validation: Only SELECT queries
      const trimmedQuery = context.sql.trim().toLowerCase();
      if (!trimmedQuery.startsWith('select')) {
        throw new Error('Only SELECT queries are allowed');
      }

      // Validate table whitelist
      const queryUpper = context.sql.toUpperCase();
      for (const table of WHITELISTED_TABLES) {
        // Allow queries that reference whitelisted tables
      }
      // Check for forbidden keywords
      const forbiddenKeywords = ['DROP', 'DELETE', 'INSERT', 'UPDATE', 'ALTER', 'CREATE', 'TRUNCATE', 'GRANT', 'REVOKE'];
      for (const keyword of forbiddenKeywords) {
        if (queryUpper.includes(keyword)) {
          throw new Error(`Forbidden keyword detected: ${keyword}`);
        }
      }

      // Ensure LIMIT clause (max 500)
      let finalQuery = context.sql.trim();
      if (!trimmedQuery.includes('limit')) {
        finalQuery += ' LIMIT 500';
      } else {
        // Verify LIMIT is not greater than 500
        const limitMatch = finalQuery.match(/LIMIT\s+(\d+)/i);
        if (limitMatch && parseInt(limitMatch[1]) > 500) {
          finalQuery = finalQuery.replace(/LIMIT\s+\d+/i, 'LIMIT 500');
        }
      }

      await client.connect();
      const result = await client.query(finalQuery);

      return {
        success: true,
        data: result.rows,
        rowCount: result.rowCount || 0,
        executedQuery: finalQuery,
      };
    } catch (err) {
      return {
        success: false,
        data: [],
        rowCount: 0,
        executedQuery: context.sql,
        error: err instanceof Error ? err.message : String(err),
      };
    } finally {
      await client.end();
    }
  }
});
```

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

```typescript
import { Agent } from '@mastra/core/agent';
import { Memory } from '@mastra/memory';
import { LibSQLStore } from '@mastra/libsql';
import { databaseIntrospectionTool } from '../tools/database-introspection-tool';
import { sqlGenerationTool } from '../tools/sql-generation-tool';
import { sqlExecutionTool } from '../tools/sql-execution-tool';

export const sqlAgent = new Agent({
  name: 'Shopsys SQL Assistant',
  instructions: `You are a helpful database assistant for the Shopsys e-commerce platform.

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

SHOPSYS CONTEXT:
- Multi-domain platform: domain_id (1, 2, 3...) represents different storefronts
- Multi-language: locale ('en', 'cs', 'sk') for translations
- Products have variants, parameters, categories, brands
- Orders have items, payments, transport methods
- Soft deletes: deleted = FALSE to exclude removed records
- Use product_visibilities for proper product filtering

QUERY GUIDELINES:
- Always use LIMIT 500 (automatically enforced)
- Use ILIKE for case-insensitive searches
- Join translations with locale filter: JOIN product_translations pt ON pt.translatable_id = p.id AND pt.locale = 'en'
- Filter by domain when relevant: JOIN product_domains pd ON pd.product_id = p.id AND pd.domain_id = 1
- Exclude soft-deleted: WHERE deleted = FALSE

RESPONSE FORMAT:
1. **Generated SQL**: Show the query in a code block
2. **Explanation**: Describe what the query does in business terms
3. **Wait for approval**: Ask user to approve, modify, or cancel
4. After approval: Execute and present results
5. **Results**: Format as a table with clear column headers
6. **Insights**: Highlight interesting patterns or suggest follow-up questions

EXAMPLE INTERACTION:
User: "Show me top 10 products by order count"

You: "I'll generate a query to find the top 10 products by order count.

\`\`\`sql
SELECT
  p.id,
  pt.name,
  COUNT(oi.id) as order_count
FROM products p
JOIN product_translations pt ON pt.translatable_id = p.id AND pt.locale = 'en'
JOIN order_items oi ON oi.product_id = p.id
WHERE p.deleted = FALSE
GROUP BY p.id, pt.name
ORDER BY order_count DESC
LIMIT 10;
\`\`\`

This query counts how many times each product appears in order items and returns the top 10.

**Ready to execute this query? Reply 'yes' to proceed, or suggest modifications.**"

[User approves]

You: [Execute query and show results as formatted table]

Be conversational, helpful, and educational. Explain database concepts when relevant.`,
  model: 'openai/gpt-4o-mini',
  tools: {
    databaseIntrospectionTool,
    sqlGenerationTool,
    sqlExecutionTool,
  },
  memory: new Memory({
    storage: new LibSQLStore({
      url: 'file:../mastra.db', // Shared with weather agent
    }),
  }),
});
```

#### 2. Register Agent in Mastra Instance
**File**: `mastra-service/src/mastra/index.ts`

**Changes**: Add sqlAgent to registered agents

```typescript
import { Mastra } from '@mastra/core/mastra';
import { LibSQLStore } from '@mastra/libsql';
import { weatherAgent } from './agents/weather-agent';
import { sqlAgent } from './agents/sql-agent';

export const mastra = new Mastra({
  storage: new LibSQLStore({
    url: ':memory:',
  }),
  agents: {
    weatherAgent,
    sqlAgent, // New SQL agent
  },
});
```

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

```php
<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class MastraController extends AdminBaseController
{
    private const string SESSION_THREAD_KEY = 'mastra_thread_id';
    private const string SESSION_SQL_THREAD_KEY = 'mastra_sql_thread_id'; // Separate thread for SQL agent

    // ... existing dashboardAction(), newConversationAction() ...

    /**
     * SQL database query chat interface
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/sql')]
    public function sqlDashboardAction(Request $request): Response
    {
        $threadId = $this->getOrCreateSqlThreadId($request);
        $resourceId = $this->getResourceId();

        return $this->render('@ShopsysAdministration/content/mastra/sql.html.twig', [
            'threadId' => $threadId,
            'resourceId' => $resourceId,
        ]);
    }

    /**
     * Start new SQL conversation
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/mastra/sql/new-conversation')]
    public function newSqlConversationAction(Request $request): Response
    {
        $request->getSession()->remove(self::SESSION_SQL_THREAD_KEY);

        return $this->redirectToRoute('admin_mastra_sqldashboard');
    }

    /**
     * Get or create thread ID for SQL agent (separate from weather agent)
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    protected function getOrCreateSqlThreadId(Request $request): string
    {
        $session = $request->getSession();
        $threadId = $session->get(self::SESSION_SQL_THREAD_KEY);

        if ($threadId === null) {
            $user = $this->getUser();
            $userId = $user ? $user->getUserIdentifier() : 'anonymous';
            $threadId = sprintf('sql_thread_%s_%s', $userId, bin2hex(random_bytes(8)));
            $session->set(self::SESSION_SQL_THREAD_KEY, $threadId);
        }

        return $threadId;
    }

    // ... existing getResourceId(), getOrCreateThreadId() methods remain unchanged
}
```

#### 2. Create SQL Chat Template
**File**: `packages/administration/templates/content/mastra/sql.html.twig`

**Purpose**: Chat interface with SQL preview and approval workflow

```twig
{% extends '@ShopsysAdministration/layout/layout_with_panel.html.twig' %}

{% block title %}- {{ 'SQL Assistant'|trans }}{% endblock %}

{% block pre_title %}{{ 'Integrations'|trans }}{% endblock %}
{% block h1 %}{{ 'SQL Database Assistant'|trans }}{% endblock %}

{% block main_content %}
    {# Chat Container #}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ 'Conversation'|trans }}</h3>
            <div>
                <small class="text-muted me-3">
                    {{ 'Resource'|trans }}: <code>{{ resourceId }}</code> |
                    {{ 'Thread'|trans }}: <code>{{ threadId }}</code>
                </small>
                <a href="{{ url('admin_mastra_newsqlconversation') }}" class="btn btn-sm btn-outline-secondary">
                    {{ 'New Conversation'|trans }}
                </a>
            </div>
        </div>
        <div class="card-body" style="max-height: 600px; overflow-y: auto; background-color: #f8f9fa;" id="chat-container">
            <div id="messages-list">
                <div class="alert alert-info">
                    <h5>{{ 'Welcome to SQL Assistant'|trans }}</h5>
                    <p>{{ 'Ask questions about your Shopsys database in natural language.'|trans }}</p>
                    <p><strong>{{ 'Examples:'|trans }}</strong></p>
                    <ul>
                        <li>{{ 'Show me top 10 best-selling products'|trans }}</li>
                        <li>{{ 'How many orders were placed last month?'|trans }}</li>
                        <li>{{ 'List categories with the most products'|trans }}</li>
                        <li>{{ 'What are the most popular brands?'|trans }}</li>
                    </ul>
                    <p class="mb-0"><em>{{ 'All queries will be shown for review before execution.'|trans }}</em></p>
                </div>
            </div>
        </div>
    </div>

    {# Input Form #}
    <div class="card">
        <div class="card-body">
            <form id="mastra-form" onsubmit="return false;">
                <div class="mb-3">
                    <label for="message-input" class="form-label">{{ 'Your question'|trans }}</label>
                    <input id="message-input"
                           class="form-control"
                           type="text"
                           placeholder="{{ 'Ask about products, orders, customers...'|trans }}"
                    />
                </div>
                <button id="send-btn" class="btn btn-primary" type="submit">
                    {{ 'Send'|trans }}
                </button>
            </form>
        </div>
    </div>

    <style>
        .message {
            padding: 12px 16px;
            margin-bottom: 12px;
            border-radius: 8px;
            max-width: 90%;
        }
        .message-user {
            background-color: #0d6efd;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .message-assistant {
            background-color: white;
            border: 1px solid #dee2e6;
            margin-right: auto;
        }
        .message-loading {
            background-color: #e9ecef;
            margin-right: auto;
            font-style: italic;
            color: #6c757d;
        }
        .message-error {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
            margin-right: auto;
        }
        .message-sql-preview {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            margin-right: auto;
        }
        .sql-code {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 8px 0;
        }
        .sql-approval-buttons {
            margin-top: 12px;
        }
        .results-table {
            margin-top: 12px;
            overflow-x: auto;
        }
        .results-table table {
            width: 100%;
            font-size: 13px;
        }
    </style>

    <script>
        (function () {
            const threadId = '{{ threadId|e('js') }}';
            const resourceId = '{{ resourceId|e('js') }}';
            const form = document.getElementById('mastra-form');
            const sendBtn = document.getElementById('send-btn');
            const input = document.getElementById('message-input');
            const messagesList = document.getElementById('messages-list');
            const chatContainer = document.getElementById('chat-container');

            let conversationHistory = [];
            let pendingQuery = null; // Store pending SQL for approval

            function scrollToBottom() {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            function createMessageElement(role, content, type = 'normal') {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message';

                if (type === 'loading') {
                    messageDiv.className += ' message-loading';
                } else if (type === 'error') {
                    messageDiv.className += ' message-error';
                } else if (type === 'sql-preview') {
                    messageDiv.className += ' message-sql-preview';
                } else if (role === 'user') {
                    messageDiv.className += ' message-user';
                } else {
                    messageDiv.className += ' message-assistant';
                }

                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';

                if (typeof content === 'string') {
                    contentDiv.innerHTML = content; // Allow HTML for formatted content
                } else {
                    contentDiv.appendChild(content); // Append DOM element
                }

                messageDiv.appendChild(contentDiv);
                return messageDiv;
            }

            function addMessage(role, content, type = 'normal') {
                // Clear welcome message on first interaction
                const welcomeAlert = messagesList.querySelector('.alert-info');
                if (welcomeAlert) {
                    welcomeAlert.remove();
                }

                const messageEl = createMessageElement(role, content, type);
                messagesList.appendChild(messageEl);
                scrollToBottom();
                return messageEl;
            }

            function formatSqlPreview(sql, explanation) {
                const container = document.createElement('div');

                const title = document.createElement('strong');
                title.textContent = '{{ 'Generated SQL Query:'|trans }}';
                container.appendChild(title);

                const sqlCode = document.createElement('div');
                sqlCode.className = 'sql-code';
                sqlCode.textContent = sql;
                container.appendChild(sqlCode);

                if (explanation) {
                    const explDiv = document.createElement('div');
                    explDiv.innerHTML = '<strong>{{ 'Explanation:'|trans }}</strong> ' + explanation;
                    container.appendChild(explDiv);
                }

                const buttonsDiv = document.createElement('div');
                buttonsDiv.className = 'sql-approval-buttons';

                const approveBtn = document.createElement('button');
                approveBtn.className = 'btn btn-success btn-sm me-2';
                approveBtn.textContent = '{{ 'Execute Query'|trans }}';
                approveBtn.onclick = () => executeApprovedQuery(sql);

                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'btn btn-secondary btn-sm';
                cancelBtn.textContent = '{{ 'Cancel'|trans }}';
                cancelBtn.onclick = () => {
                    addMessage('user', '{{ 'Query cancelled'|trans }}');
                    pendingQuery = null;
                };

                buttonsDiv.appendChild(approveBtn);
                buttonsDiv.appendChild(cancelBtn);
                container.appendChild(buttonsDiv);

                return container;
            }

            function formatResults(data, rowCount) {
                if (!data || data.length === 0) {
                    return '<em>{{ 'No results found'|trans }}</em>';
                }

                const container = document.createElement('div');
                container.className = 'results-table';

                const summary = document.createElement('p');
                summary.innerHTML = `<strong>{{ 'Results:'|trans }}</strong> ${rowCount} {{ 'rows returned'|trans }}`;
                container.appendChild(summary);

                const table = document.createElement('table');
                table.className = 'table table-sm table-bordered table-striped';

                // Header
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                const firstRow = data[0];
                for (const key in firstRow) {
                    const th = document.createElement('th');
                    th.textContent = key;
                    headerRow.appendChild(th);
                }
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Body
                const tbody = document.createElement('tbody');
                for (const row of data) {
                    const tr = document.createElement('tr');
                    for (const key in row) {
                        const td = document.createElement('td');
                        td.textContent = row[key] !== null ? row[key] : '(null)';
                        tr.appendChild(td);
                    }
                    tbody.appendChild(tr);
                }
                table.appendChild(tbody);
                container.appendChild(table);

                return container;
            }

            async function sendMessage() {
                const message = input.value.trim();
                if (!message) {
                    return;
                }

                input.value = '';
                sendBtn.disabled = true;

                addMessage('user', message);
                conversationHistory.push({ role: 'user', content: message });

                const loadingEl = addMessage('assistant', '{{ 'Processing your query...'|trans }}', 'loading');

                try {
                    const res = await fetch('/mastra/api/agents/sqlAgent/generate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            messages: conversationHistory,
                            threadId: threadId,
                            resourceId: resourceId,
                        })
                    });

                    loadingEl.remove();

                    if (!res.ok) {
                        const text = await res.text().catch(() => '');
                        throw new Error(`HTTP ${res.status}: ${text}`);
                    }

                    const json = await res.json();
                    const responseText = json?.text || '';

                    // Check if response contains SQL (simple heuristic: contains "SELECT")
                    if (responseText.toUpperCase().includes('SELECT')) {
                        // Extract SQL from code block if present
                        const sqlMatch = responseText.match(/```sql\n([\s\S]*?)\n```/);
                        const sql = sqlMatch ? sqlMatch[1] : responseText;

                        // Store for approval
                        pendingQuery = sql;

                        // Show SQL preview with approval buttons
                        addMessage('assistant', formatSqlPreview(sql, '{{ 'Review and approve this query'|trans }}'), 'sql-preview');
                    } else {
                        // Regular assistant response (no SQL)
                        addMessage('assistant', responseText);
                        conversationHistory.push({ role: 'assistant', content: responseText });
                    }

                } catch (err) {
                    loadingEl.remove();
                    const errorMessage = '{{ 'Error'|trans }}: ' + (err?.message || err);
                    addMessage('assistant', errorMessage, 'error');
                } finally {
                    sendBtn.disabled = false;
                    input.focus();
                }
            }

            async function executeApprovedQuery(sql) {
                if (!sql) return;

                const loadingEl = addMessage('assistant', '{{ 'Executing query...'|trans }}', 'loading');

                try {
                    // Send execution request with the approved SQL
                    const executionMessage = `Execute this SQL: ${sql}`;
                    conversationHistory.push({ role: 'user', content: executionMessage });

                    const res = await fetch('/mastra/api/agents/sqlAgent/generate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            messages: conversationHistory,
                            threadId: threadId,
                            resourceId: resourceId,
                        })
                    });

                    loadingEl.remove();

                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }

                    const json = await res.json();

                    // Check if toolResults contains execution results
                    if (json.toolResults && json.toolResults.length > 0) {
                        const executionResult = json.toolResults.find(t => t.toolName === 'sql-execution');
                        if (executionResult && executionResult.result.success) {
                            addMessage('assistant', formatResults(executionResult.result.data, executionResult.result.rowCount));
                        } else {
                            addMessage('assistant', executionResult?.result?.error || '{{ 'Query execution failed'|trans }}', 'error');
                        }
                    } else {
                        addMessage('assistant', json?.text || '{{ 'No results'|trans }}');
                    }

                    conversationHistory.push({ role: 'assistant', content: json?.text || '' });
                    pendingQuery = null;

                } catch (err) {
                    loadingEl.remove();
                    addMessage('assistant', '{{ 'Execution error'|trans }}: ' + (err?.message || err), 'error');
                }
            }

            // Event listeners
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                sendMessage();
            });

            sendBtn.addEventListener('click', (e) => {
                e.preventDefault();
                sendMessage();
            });

            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });

            input.focus();
        })();
    </script>
{% endblock %}
```

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

**Changes**: Add constant around line 189

```php
public const string MASTRA_SQL_DASHBOARD = 'mastra_sql_dashboard';
```

#### 2. Add Menu Item
**File**: `packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php`

**Changes**: Add menu item in `createIntegrationsMenu()` around line 901

```php
protected function createIntegrationsMenu(): ItemInterface
{
    $integrationsMenu = $this->menuFactory->createItem(static::ROOT_INTEGRATIONS, ['label' => t('Integrations')]);
    $integrationsMenu->setExtra('icon', 'puzzle');

    $integrationsMenu->addChild(static::LIST_FEED, ['route' => 'admin_feed_list', 'label' => t('XML Feeds')]);
    $integrationsMenu->addChild(static::MASTRA_DASHBOARD, ['route' => 'admin_mastra_dashboard', 'label' => t('Mastra Assistant')]);
    $integrationsMenu->addChild(static::MASTRA_SQL_DASHBOARD, ['route' => 'admin_mastra_sqldashboard', 'label' => t('SQL Assistant')]);

    $heurekaMenu = $integrationsMenu->addChild(static::SECTION_HEUREKA, ['label' => t('Heureka')]);
    // ...

    return $integrationsMenu;
}
```

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
Configure database connection string from Symfony parameters and pass to Mastra tools via runtime context.

### Changes Required

#### 1. Create Database Connection Helper in Mastra Service
**File**: `mastra-service/src/lib/db-connection.ts`

**Purpose**: Build connection string from environment variables

```typescript
export function getShopsysConnectionString(): string {
  // Read from environment variables passed from docker-compose
  const host = process.env.DATABASE_HOST || 'postgres';
  const port = process.env.DATABASE_PORT || '5432';
  const database = process.env.DATABASE_NAME || 'shopsys';
  const user = process.env.DATABASE_USER || 'root';
  const password = process.env.DATABASE_PASSWORD || 'root';

  return `postgresql://${user}:${password}@${host}:${port}/${database}`;
}
```

#### 2. Update Docker Compose Configuration
**File**: `docker-compose.yml`

**Changes**: Add database environment variables to mastra service

```yaml
mastra:
  # ... existing configuration
  environment:
    OPENAI_API_KEY: ${OPENAI_API_KEY}
    NODE_ENV: development
    DATABASE_HOST: postgres
    DATABASE_PORT: 5432
    DATABASE_NAME: shopsys
    DATABASE_USER: root
    DATABASE_PASSWORD: root
```

#### 3. Update Tools to Use Connection Helper
**File**: `mastra-service/src/mastra/tools/database-introspection-tool.ts`

**Changes**: Use connection helper instead of accepting connection string as input

```typescript
import { getShopsysConnectionString } from '../../lib/db-connection';

export const databaseIntrospectionTool = createTool({
  id: 'database-introspection',
  description: 'Analyzes Shopsys database schema',
  inputSchema: z.object({}), // No input needed - connection is automatic
  // ...
  execute: async () => {
    const connectionString = getShopsysConnectionString();
    const client = new Client({ connectionString, /* ... */ });
    // ... rest of implementation
  }
});
```

**Apply same change to**: `sql-generation-tool.ts`, `sql-execution-tool.ts`

### Success Criteria

#### Automated Verification:
- [ ] Mastra service starts without connection errors: `docker compose logs mastra`
- [ ] Tools can connect to database
- [ ] TypeScript compiles: `docker compose exec mastra pnpm run build`

#### Manual Verification:
- [ ] Introspection tool returns Shopsys tables
- [ ] Execution tool successfully runs queries
- [ ] No hardcoded passwords in code (only in .env)

---

## Phase 6: Testing and Security Validation

### Overview
Comprehensive testing of security constraints, query generation, and user experience.

### Testing Checklist

#### Security Testing:
- [ ] **Table Whitelist**: Verify `administrators` table excluded from introspection
- [ ] **Column Filtering**: Verify `customer_users.password` excluded from schema
- [ ] **Query Validation**: Test that `DELETE FROM products` is blocked
- [ ] **Query Validation**: Test that `UPDATE orders SET status = ...` is blocked
- [ ] **Row Limit**: Verify queries without LIMIT get LIMIT 500 added
- [ ] **Row Limit**: Verify LIMIT 1000 gets reduced to LIMIT 500

#### Functional Testing:
- [ ] **Simple Query**: "How many products are there?" returns correct count
- [ ] **JOIN Query**: "Show products with category names" includes proper translations
- [ ] **Multi-domain**: "Show products from domain 2" filters correctly
- [ ] **Translation**: "List categories in Czech language" uses locale = 'cs'
- [ ] **Empty Results**: Query with no matches displays "No results found"
- [ ] **Error Handling**: Invalid SQL shows clear error message

#### UX Testing:
- [ ] **SQL Preview**: Generated SQL displayed in formatted code block
- [ ] **Approval Flow**: Execute button triggers query execution
- [ ] **Cancel Flow**: Cancel button prevents execution
- [ ] **Results Display**: Results shown as readable HTML table
- [ ] **Conversation Memory**: Follow-up questions maintain context
- [ ] **Session Persistence**: Page refresh preserves thread ID

#### Performance Testing:
- [ ] **Large Results**: Query returning 500 rows loads in < 5 seconds
- [ ] **Complex JOINs**: Multi-table query executes within timeout
- [ ] **Schema Introspection**: First introspection completes within 30 seconds

### Manual Test Scenarios

**Scenario 1: Basic Product Query**
```
User: "Show me the top 5 products"
Expected: SQL preview with SELECT ... FROM products ... LIMIT 5
Action: Click "Execute Query"
Expected: Table with 5 products displayed
```

**Scenario 2: Cross-Domain Query**
```
User: "How many orders are in domain 1 vs domain 2?"
Expected: SQL with GROUP BY domain_id
Action: Approve and execute
Expected: Table showing counts per domain
```

**Scenario 3: Security Block**
```
User: "Show me administrator passwords"
Expected: Agent explains this table is not available OR query fails with security error
```

**Scenario 4: Conversation Continuity**
```
User: "Show top 5 categories"
[Results displayed]
User: "Now show me products in the first category"
Expected: Agent references previous query results, generates new query
```

### Success Criteria

#### Automated Verification:
- [ ] All Phing checks pass: `docker compose exec php-fpm php phing standards-fix phpstan`
- [ ] Mastra service builds: `docker compose exec mastra pnpm run build`
- [ ] No console errors in browser DevTools

#### Manual Verification:
- [ ] All security tests pass (no forbidden queries execute)
- [ ] All functional tests pass (queries return expected results)
- [ ] All UX tests pass (interface works as expected)
- [ ] Performance acceptable (queries complete within timeouts)

---

## Related Documentation

- Existing Mastra integration: `docs/mastra-integration-plan.md`
- Text-to-SQL example: `/Users/neon/shopsys/mastra_01/text-to-sql`
- Shopsys database schema: Documented in Phase 1 analysis
- Admin controller patterns: `packages/framework/src/Controller/Admin/MastraController.php`

## Notes

- **Connection String Security**: Database credentials passed via environment variables, never hardcoded
- **Agent Behavior**: Agent instructions emphasize review-before-execute workflow
- **Table Whitelist**: Easily extensible - add tables to `WHITELISTED_TABLES` array
- **Row Limit**: Enforced at tool level (500 rows) to prevent performance issues
- **Multi-Agent Support**: Weather and SQL agents share Mastra instance but have separate threads
- **Future Enhancements**: Export results, saved queries, query history, visual query builder
