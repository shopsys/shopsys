#!/usr/bin/env node

const generateWebFont = require('../commands/svg/generateWebFont');
const sources = require('./helpers/sources');

generateWebFont(
    'admin',
    sources.getFrameworkNodeModulesDir() + '/public/admin/svg/',
    './web/public/admin/svg/'
);
