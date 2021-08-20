// eslint-disable-next-line @typescript-eslint/no-var-requires
const intervalPlural = require('i18next-intervalplural-postprocessor');

module.exports = {
    i18n: {
        locales: ['cs', 'sk', 'en'],
        defaultLocale: 'cs',
        localeDetection: false,
        serializeConfig: false,
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
