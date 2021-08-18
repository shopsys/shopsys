// eslint-disable-next-line @typescript-eslint/no-var-requires
const intervalPlural = require('i18next-intervalplural-postprocessor');

module.exports = {
    i18n: {
        locales: ['cs', 'sk', 'en'],
        defaultLocale: 'cs',
        localeDetection: false,
        serializeConfig: false,
        domains: [
            {
                domain: '127.0.0.1:3000',
                backendHost: '127.0.0.1',
                defaultLocale: 'cs',
                http: true,
                currencyCode: 'CZK',
                http: true,
            },
            {
                domain: '127.0.0.2:3000',
                backendHost: '127.0.0.2',
                defaultLocale: 'sk',
                http: true,
                currencyCode: 'EUR',
                http: true,
            },
        ],
        interpolation: {
            format: (value, format, lng) => {
                if (format === 'formatPrice') {
                    return Intl.NumberFormat(lng, {
                        style: 'currency',
                        currency: value.currencyCode,
                    }).format(value.price);
                }
                return value;
            },
        },
        use: [intervalPlural],
    },
};
