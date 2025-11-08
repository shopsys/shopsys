export function getShopsysConnectionString(): string {
  // Read from environment variables passed from docker-compose
  const host = process.env.DATABASE_HOST || 'postgres';
  const port = process.env.DATABASE_PORT || '5432';
  const database = process.env.DATABASE_NAME || 'shopsys';
  const user = process.env.DATABASE_USER || 'root';
  const password = process.env.DATABASE_PASSWORD || 'root';

  return `postgresql://${user}:${password}@${host}:${port}/${database}`;
}
