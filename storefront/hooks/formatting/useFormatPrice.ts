import { useSettingsQueryApi } from 'graphql/generated';
import { formatPrice } from 'helpers/formaters/formatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useShopsysSelector } from 'redux/main';

type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const t = useTypedTranslationFunction();
    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-first' });
    const { defaultLocale = 'en' } = useShopsysSelector((state) => state.domain);
    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = data?.settings?.pricing ?? {};
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : Number.parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
