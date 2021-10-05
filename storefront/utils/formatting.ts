import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export function formatPrice(price: number, currencyCode: string, options?: { explicitZero?: boolean }): string {
    const t = useTypedTranslationFunction();

    if (price === 0 && !options?.explicitZero) {
        return t('Free');
    }

    return t('{{ value, formatPrice }}', {
        value: { price: price, currencyCode: currencyCode },
    });
}
