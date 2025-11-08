import { createTool } from '@mastra/core/tools';
import { z } from 'zod';

export const schemaFormatterTool = createTool({
  id: 'schema-formatter',
  description: 'Formats database schema into readable description for SQL generation',
  inputSchema: z.object({
    schema: z.any().describe('Database schema metadata from introspection')
  }),
  outputSchema: z.object({
    formattedSchema: z.string().describe('Human-readable schema description'),
    tableCount: z.number().describe('Number of tables'),
    guidelines: z.string().describe('SQL generation guidelines for Shopsys'),
  }),
  execute: async ({ context }) => {
    const { tables, columns, relationships, rowCounts } = context.schema;

    let description = 'AVAILABLE TABLES:\n';
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

    const guidelines = `SHOPSYS SQL GUIDELINES:

CRITICAL RULES:
1. ONLY generate SELECT queries (no INSERT, UPDATE, DELETE, DROP, CREATE, ALTER)
2. ALWAYS use proper table and column qualification (table_name.column_name)
3. ALWAYS add LIMIT 500 to prevent large result sets
4. Use ILIKE for case-insensitive text searches in PostgreSQL

SHOPSYS PATTERNS:
- Multi-domain: Most entities have *_domains tables with domain_id
- Translations: Most entities have *_translations tables with locale ('en', 'cs', 'sk')
- Domain-specific: product_domains, category_domains store SEO, descriptions, VAT
- Soft deletes: Check for deleted = FALSE where applicable
- Nested categories: Use lft/rgt for hierarchical queries

COMMON JOINS:
- Products + translations: JOIN product_translations pt ON pt.translatable_id = p.id AND pt.locale = 'en'
- Products + domains: JOIN product_domains pd ON pd.product_id = p.id AND pd.domain_id = 1
- Categories + translations: JOIN category_translations ct ON ct.translatable_id = c.id AND ct.locale = 'en'

PERFORMANCE:
- Use indexed columns in WHERE: id, domain_id, catnum, email, lft, rgt
- Prefer product_visibilities over complex filtering
- Always include LIMIT clause (max 500 rows)`;

    return {
      formattedSchema: description,
      tableCount: tables.length,
      guidelines,
    };
  }
});
