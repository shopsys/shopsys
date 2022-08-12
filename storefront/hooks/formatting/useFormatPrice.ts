import { useSettingsQueryApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useShopsysSelector } from 'redux/main';
import { formatPrice } from 'utils/formatting';

type ReturnFunctionType = (price: number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): ReturnFunctionType => {
    const t = useTypedTranslationFunction();
    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-first' });
    const { defaultLocale = 'en' } = useShopsysSelector((state) => state.domain);
    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = data?.settings?.pricing ?? {};

    return (price: number, options?: { explicitZero?: boolean }) =>
        formatPrice(price, defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
