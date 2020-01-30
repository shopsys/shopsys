const fs = require('fs');
const babelParser = require('@babel/parser');
const traverse = require('@babel/traverse').default;

const TRANS_DOMAIN_POSITION = 2;
const TRANS_LOCALE_POSITION = 3;

const TRANSCHOICE_DOMAIN_POSITION = 3;
const TRANSCHOICE_LOCALE_POSITION = 4;

const createObject = (args, domainPosition, localePosition, filePath, line) => {
    return {
        id: args[0].value,
        domain: args[domainPosition] && args[domainPosition].value,
        locale: args[localePosition] && args[localePosition].value,
        source: filePath,
        line: line
    };
};

function parseFile (filePath) {
    const translations = [];
    const ast = babelParser.parse(fs.readFileSync(filePath).toString(), { sourceType: 'module' });

    traverse(ast, {
        CallExpression (path) {
            if (path.node.callee.object && path.node.callee.object.name === 'Translator') {
                const isTransMethod = path.node.callee.property.name === 'trans'
                const transObject = createObject(
                    path.node.arguments,
                    isTransMethod ? TRANS_DOMAIN_POSITION : TRANSCHOICE_DOMAIN_POSITION,
                    isTransMethod ? TRANS_LOCALE_POSITION : TRANSCHOICE_LOCALE_POSITION,
                    filePath,
                    path.node.callee.loc.start.line
                );
                translations.push(transObject);
            }
        }
    });

    return translations;
}

module.exports = parseFile;
