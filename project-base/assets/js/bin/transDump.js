#!/usr/bin/env node

const fs = require('fs');
const processTransDump = require('../commands/translations/processDump');
const defaultDirWithJsFiles = './assets/js/';
const dirForExportDumpFile = './var/translations/';

const dirsWithJsFiles = process.argv.slice(2).map(parameter => {
    const keyValueParameter = parameter.split('=');
    if (keyValueParameter[0] === 'source-dir') {
        return keyValueParameter[1];
    }
});

if (dirsWithJsFiles.length === 0) {
    dirsWithJsFiles.push(defaultDirWithJsFiles + '**/*.js');
}

if (!fs.existsSync(dirForExportDumpFile)) {
    fs.mkdirSync(dirForExportDumpFile);
}

processTransDump(dirsWithJsFiles, dirForExportDumpFile);
