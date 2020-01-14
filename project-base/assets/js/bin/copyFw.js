#!/usr/bin/env node

const fileSystem = require('fs');
const nodeCopyCommand = require('ncp').ncp;

nodeCopyCommand.limit = 16;

let sourceDir = './vendor/shopsys/framework/assets/js';
if (!fileSystem.existsSync(sourceDir)) {
    sourceDir = '../packages/framework/assets/js';
}

console.log('Started copying framework assets to project-base ...');
nodeCopyCommand(sourceDir, './assets/js/framework', function (err) {
    if (err) {
        return console.error(err);
    }
    console.log('Copying framework assets was successful!');
});
