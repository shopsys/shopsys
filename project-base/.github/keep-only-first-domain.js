const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, '../storefront/next.config.js');

let fileContent = fs.readFileSync(filePath, 'utf8');

const domainConfigs = fileContent.match(
    /domains\s*:\s*\[[\s\S]*\]/gm
);

const firstDomain = domainConfigs[0].replace(
    /(domains\s*:\s*\[\s*\{\s*publicGraphqlEndpoint\s*:\s*process\.env\.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1[\s\S]*},\s)\s*{\s*publicGraphqlEndpoint\s*:\s*process\.env\.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2[\s\S]*},\s( +])/gm,
    '$1$2'
);

fileContent = fileContent.replace(
    /([\s\S]*)(domains\s*:\s*\[[\s\S]*\])([\s\S]*)/gm,
    `\$1${firstDomain}\$3`
);

fs.writeFileSync(filePath, fileContent);
