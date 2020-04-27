module.exports = {
    presets: [
        [
            '@babel/preset-env',
            {
                targets: {
                    node: 'current',
                    browsers: [
                        'safari >= 8'
                    ]
                }
            }
        ]
    ],
    plugins: ['@babel/plugin-proposal-object-rest-spread']
};
