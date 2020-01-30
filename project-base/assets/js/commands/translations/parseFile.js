const fs = require('fs');
const babelParser = require('@babel/parser');
const traverse = require('@babel/traverse').default;

const createTransObject = (args) => {
    return {
        id: args[0].value,
        domain: args[2] && args[2].value,
        locale: args[3] && args[3].value
    };
};

const createTransChoiceObject = (args) => {
    return {
        id: args[0].value,
        domain: args[3] && args[3].value,
        locale: args[4] && args[4].value
    };
};

function parseFile (filePath) {
    const translations = [];
    const ast = babelParser.parse(fs.readFileSync(filePath).toString(), { sourceType: 'module' });

    traverse(ast, {
        CallExpression (path) {
            if (path.node.callee.object && path.node.callee.object.name === 'Translator') {
                const transObject = path.node.callee.property.name === 'trans'
                    ? createTransObject(path.node.arguments)
                    : createTransChoiceObject(path.node.arguments);

                translations.push(transObject);
            }
        }
    });

    return translations;
}

module.exports = parseFile;
