import { createTool } from '@mastra/core/tools';
import { Client } from 'pg';
import { z } from 'zod';
import { getShopsysConnectionString } from '../../lib/db-connection';

// Whitelisted tables based on user selection: "Recommended safe set"
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

// Sensitive columns to exclude even from whitelisted tables
const EXCLUDED_COLUMNS: Record<string, string[]> = {
  'customer_users': ['password', 'reset_password_hash'],
  'administrators': ['password', 'login_token']
};

export const databaseIntrospectionTool = createTool({
  id: 'database-introspection',
  description: 'Analyzes Shopsys database schema for whitelisted tables',
  inputSchema: z.object({}),
  outputSchema: z.object({
    tables: z.array(z.any()),
    columns: z.array(z.any()),
    relationships: z.array(z.any()),
    indexes: z.array(z.any()),
    rowCounts: z.record(z.number()),
  }),
  execute: async () => {
    const connectionString = getShopsysConnectionString();
    const client = new Client({
      connectionString,
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
