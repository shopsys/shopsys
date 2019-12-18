const fs = require('fs');
const babelParser = require('@babel/parser');
const traverse = require('@babel/traverse').default;

function parseFile (filePath) {
    const translations = [];
    const ast = babelParser.parse(fs.readFileSync(filePath).toString(),{sourceType: 'module'});

    traverse(ast, {
        CallExpression(path) {
            if (path.node.callee.object && path.node.callee.object.name === 'Translator') {
                const value = path.node.arguments[0].value;
                translations.push(value);
            }

        }
    });

    return translations;
}

module.exports = parseFile;
