const fs = require('fs');

const getFrameworkNodeModulesDir = () => {
    if (isMonorepo()) {
        return '../packages/framework/assets';
    }

    return './node_modules/@shopsys/framework';
};

const isMonorepo = () => {
    return fs.existsSync('../packages');
};

module.exports = { getFrameworkNodeModulesDir };
