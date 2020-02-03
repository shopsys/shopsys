#!/usr/bin/env node

const fs = require('fs');
const processTransDump = require('../commands/translations/processDump');
const defaultDirWithJsFiles = './assets/js/';

const dirsWithJsFiles = process.argv.slice(2).map(parameter => {
    const keyValueParameter = parameter.split('=');
    if (keyValueParameter[0] === 'source-dir') {
        return keyValueParameter[1];
    }
}).filter(item => item !== undefined);

if (dirsWithJsFiles.length === 0) {
    dirsWithJsFiles.push(defaultDirWithJsFiles + '**/*.js');
}

let dirForExport = process.argv.slice(2).map(parameter => {
    const keyValueParameter = parameter.split('=');
    if (keyValueParameter[0] === 'export-dir') {
        return keyValueParameter[1];
    }
}).filter(item => item !== undefined)[0];

if (Array.isArray(dirForExport)) {
    dirForExport = dirForExport[0];
}

if (!fs.existsSync(dirForExport)) {
    fs.mkdirSync(dirForExport, { recursive: true });
}

processTransDump(dirsWithJsFiles, dirForExport);
