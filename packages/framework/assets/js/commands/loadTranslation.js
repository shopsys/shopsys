#!/usr/bin/env node

const processTrans = require('./translations/process');
const dirWithJsFiles = './assets/js/';
const dirWithTranslations = './src/Resources/translations/';
const outputDirForExportedTranslations = dirWithJsFiles;

processTrans(dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
