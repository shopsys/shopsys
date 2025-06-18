const fs = require('node:fs');

const getPackageNodeModulesDir = (packageName) => {
    if (isMonorepo()) {
        return '../../packages/' + packageName + '/assets';
    }

    return './node_modules/@shopsys/' + packageName;
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

module.exports = { getPackageNodeModulesDir, getNodeModulesDir };
