const fs = require('node:fs');
const path = require('node:path');
const introspection = require('@urql/introspection');

const schemaFilePath = path.resolve(__dirname, './schema.graphql.json');
const schemaFileContent = fs.readFileSync(schemaFilePath, 'utf8');
const schema = JSON.parse(schemaFileContent);
const result = introspection.minifyIntrospectionQuery(schema);
const minifiedJson = JSON.stringify(result);

fs.writeFileSync('./schema-compressed.graphql.json', minifiedJson, 'utf8');
fs.unlinkSync(schemaFilePath);
