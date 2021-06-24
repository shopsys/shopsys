#!/usr/bin/env node

const generateWebFont = require('../commands/svg/generateWebFont');
const sources = require('./helpers/sources');

generateWebFont(
    'frontend',
    './assets/public/frontend/svg/'
);
generateWebFont(
    'admin',
    sources.getFrameworkNodeModulesDir() + '/public/admin/svg/',
    './web/public/admin/svg/'
);
