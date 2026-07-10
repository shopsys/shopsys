#!/usr/bin/env node

const ncp = require('ncp').ncp;
const fs = require('node:fs');
const sources = require('./helpers/sources');

const assets = [
    {
        source: `${sources.getPackageNodeModulesDir('framework')}/public/admin`,
        destination: 'web/public/admin',
    },
    {
        source: 'assets/public',
        destination: 'web/public',
    },
];

assets.forEach(item => {
    fs.mkdirSync(item.destination, { recursive: true });

    ncp(item.source, item.destination, err => {
        if (err) {
            return console.error(err);
        }
        console.log(`Source folder ${item.source} was copied.`);
    });
});
