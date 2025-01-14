module.exports = {
    presets: [
        ['@babel/preset-env', {
            "loose": true,
            "modules": false
        }],
        ['@babel/preset-typescript', { allowDeclareFields: true }]
    ],
    assumptions: {
        superIsCallableConstructor: false,
    },
};