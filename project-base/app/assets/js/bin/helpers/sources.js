const fs = require('node:fs');

const getFrameworkNodeModulesDir = () => {
    if (isMonorepo()) {
        return '../../packages/framework/assets';
    }

    return './node_modules/@shopsys/framework';
};

const isMonorepo = () => {
    return fs.existsSync('../../packages');
};

const getNodeModulesDir = () => {
    if (isMonorepo()) {
        return '../../node_modules';
    }

    return './node_modules';
};

module.exports = { getFrameworkNodeModulesDir, getNodeModulesDir };
