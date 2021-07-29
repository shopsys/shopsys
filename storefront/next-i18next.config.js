module.exports = {
    i18n: {
        locales: ['cs', 'sk', 'en'],
        defaultLocale: 'cs',
        localeDetection: false,
        domains: [
            {
                domain: '127.0.0.1:3000',
                defaultLocale: 'cs',
            },
            {
                domain: '127.0.0.2:3000',
                defaultLocale: 'sk',
            },
        ],
    },
};
