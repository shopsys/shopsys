#!/usr/bin/env node

const fs = require('fs');
const processTransDump = require('../commands/translations/processDump');
const getCliParameters = require('./helpers/getCliParameters');
const defaultDirWithJsFiles = './assets/js/';

const dirsWithJsFiles = getCliParameters(process.argv.slice(2), 'source-dir');

if (dirsWithJsFiles.length === 0) {
    dirsWithJsFiles.push(defaultDirWithJsFiles + '**/*.js');
}

let dirForExport = getCliParameters(process.argv.slice(2), 'export-dir');

if (Array.isArray(dirForExport)) {
    dirForExport = dirForExport[0];
}

if (!fs.existsSync(dirForExport)) {
    fs.mkdirSync(dirForExport, { recursive: true });
}

processTransDump(dirsWithJsFiles, dirForExport);
