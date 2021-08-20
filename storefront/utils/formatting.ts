import { useTranslation } from 'react-i18next';

export function formatPrice(price: number, currencyCode: string): string {
    const { t } = useTranslation();

    return t('{{ value, formatPrice }}', {
        value: { price: price, currencyCode: currencyCode },
    });
}
