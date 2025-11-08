import { createTool } from '@mastra/core/tools';
import { Client } from 'pg';
import { z } from 'zod';
import { getShopsysConnectionString } from '../../lib/db-connection';

const WHITELISTED_TABLES = [
  'products', 'product_translations', 'product_domains', 'product_visibilities',
  'product_parameter_values', 'categories', 'category_translations', 'category_domains',
  'brands', 'brand_translations', 'units', 'unit_translations',
  'parameters', 'parameter_translations', 'parameter_values',
  'orders', 'order_items', 'order_statuses',
  'customers', 'customer_users',
  'billing_addresses', 'delivery_addresses',
  'payments', 'payment_translations', 'transports', 'transport_translations',
  'pricing_groups', 'vats', 'currencies',
  'stores', 'store_opening_hours',
  'blog_articles', 'blog_article_translations', 'blog_categories', 'blog_category_translations',
  'flags', 'product_accessories', 'carts', 'cart_items'
];

export const sqlExecutionTool = createTool({
  id: 'sql-execution',
  description: 'Executes SELECT queries on Shopsys database with safety validation',
  inputSchema: z.object({
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
    const connectionString = getShopsysConnectionString();
    const client = new Client({
      connectionString,
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

      // Check for forbidden keywords
      const queryUpper = context.sql.toUpperCase();
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
