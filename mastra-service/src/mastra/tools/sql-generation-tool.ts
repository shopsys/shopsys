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
      temperature: 0.1,
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
