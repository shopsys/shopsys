import { Translate } from 'next-translate';

export function formatPrice(
    price: number,
    currencyCode: string,
    t: Translate,
    locale: string,
    minimumFractionDigits: number,
    options?: { explicitZero?: boolean },
): string {
    if (price === 0 && !options?.explicitZero) {
        return t('Free');
    }

    return Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits,
    }).format(price);
}
