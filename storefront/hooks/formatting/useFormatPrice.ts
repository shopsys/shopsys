import { useSettingsQueryApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { formatPrice } from 'utils/formatting';

type ReturnFunctionType = (price: number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): ReturnFunctionType => {
    const t = useTypedTranslationFunction();
    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-first' });
    const { locale = 'en' } = useRouter();
    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = data?.settings?.pricing ?? {};

    return (price: number, options?: { explicitZero?: boolean }) =>
        formatPrice(price, defaultCurrencyCode, t, locale, minimumFractionDigits, options);
};
