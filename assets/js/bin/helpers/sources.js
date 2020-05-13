const fs = require('fs');

const getFrameworkVendorDir = () => {
    if (isMonorepo()) {
        return '../packages/framework';
    }

    return './vendor/shopsys/framework';
};

const getFrameworkNodeModulesDir = () => {
    if (isMonorepo()) {
        return '../packages/framework/assets';
    }

    return './node_modules/@shopsys/framework';
};

const isMonorepo = () => {
    return fs.existsSync('../packages');
};

module.exports = { getFrameworkVendorDir, getFrameworkNodeModulesDir };
