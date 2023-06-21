import { useSettingsQueryApi } from 'graphql/generated';
import { formatPrice } from 'helpers/formaters/formatPrice';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';

type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const t = useTypedTranslationFunction();
    const [{ data }] = useQueryError(useSettingsQueryApi({ requestPolicy: 'cache-first' }));
    const { defaultLocale = 'en' } = useDomainConfig();

    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = data?.settings?.pricing ?? {};
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
