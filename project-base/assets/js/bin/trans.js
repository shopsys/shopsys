#!/usr/bin/env node

const processTrans = require('../commands/translations/process');
const getCliParameters = require('./helpers/getCliParameters');
const dirWithTranslations = './translations/*.po';
const outputDirForExportedTranslations = './assets/js/';

const dirsWithJsFiles = getCliParameters(process.argv.slice(2), 'source-dir');

if (dirsWithJsFiles.length === 0) {
    dirsWithJsFiles.push(outputDirForExportedTranslations + '**/*.js');
}

processTrans(dirsWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
