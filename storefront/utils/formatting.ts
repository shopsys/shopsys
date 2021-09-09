import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

export function formatPrice(price: number, currencyCode: string): string {
    const t = useTypedTranslationFunction();

    return t('{{ value, formatPrice }}', {
        value: { price: price, currencyCode: currencyCode },
    });
}
