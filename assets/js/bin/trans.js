#!/usr/bin/env node

const processTrans = require('../commands/translations/process');
const dirWithJsFiles = './assets/js/';
const dirWithTranslations = './translations/';
const outputDirForExportedTranslations = dirWithJsFiles;

processTrans(dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
