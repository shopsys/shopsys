/* eslint-disable @typescript-eslint/no-var-requires */
const fs = require('fs');
const path = require('path');
const introspection = require('@urql/introspection');

const schemaFilePath = path.resolve(__dirname, './schema.graphql.json');
const schemaFileContent = fs.readFileSync(schemaFilePath, 'utf8');
const schema = JSON.parse(schemaFileContent);
const result = introspection.minifyIntrospectionQuery(schema);
const minifiedJson = JSON.stringify(result);

fs.writeFileSync('./schema-compressed.graphql.json', minifiedJson, 'utf8');
