module.exports = {
    presets: [
        [
            '@babel/preset-env',
            {
                useBuiltIns: "entry",
                corejs: "3.13",
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
