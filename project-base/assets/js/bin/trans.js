#!/usr/bin/env node

const processTrans = require('../commands/translations/process');
const dirWithTranslations = './translations/*.po';
const outputDirForExportedTranslations = './assets/js/';

const dirsWithJsFiles = process.argv.slice(2).map(parameter => {
    const keyValueParameter = parameter.split('=');
    if (keyValueParameter[0] === 'source-dir') {
        return keyValueParameter[1];
    }
});

if (dirsWithJsFiles.length === 0) {
    dirsWithJsFiles.push(outputDirForExportedTranslations + '**/*.js');
}

processTrans(dirsWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
