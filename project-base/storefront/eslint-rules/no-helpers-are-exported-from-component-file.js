module.exports = {
  meta: {
    type: 'problem',
    docs: {
      description: 'Enforce that no other functions besides the component are exported from a .tsx file. ',
    },
  },
  create: function (context) {
    return {
      ExportNamedDeclaration(node) {
        if (node.declaration.type === 'VariableDeclaration') {
          for (const declaration of node.declaration.declarations) {
            if (
              declaration &&
              declaration.type === 'VariableDeclarator' &&
              declaration.init.type === 'ArrowFunctionExpression' &&
              declaration.id.type === 'Identifier' &&
              (declaration.id.name.match(/^[a-z]/) && !declaration.id.name.match(/\buse[A-Z][a-zA-Z]*\b/))
            ) {
              context.report({
                node,
                message: 'No other functions besides the component should be exported in this file. Move other functions to a helper file.',
              });
            }
          }
        }
      },
    };
  },
};
