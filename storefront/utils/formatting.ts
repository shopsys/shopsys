import { TFunction } from 'next-i18next';

export function formatPrice(
    price: number,
    currencyCode: string,
    t: TFunction,
    options?: { explicitZero?: boolean },
): string {
    if (price === 0 && !options?.explicitZero) {
        return t('Free');
    }

    return t('{{ value, formatPrice }}', {
        value: { price: price, currencyCode: currencyCode },
    });
}
