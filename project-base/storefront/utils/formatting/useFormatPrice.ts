import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { formatPrice } from 'utils/formaters/formatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type FormatPriceFunctionType = (
    price: string | number,
    options?: { explicitZero?: boolean; currencyCode?: string },
) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const { defaultLocale = 'en' } = useDomainConfig();

    const pricing = settingsData?.settings?.pricing;
    const currentCurrencyCode = pricing?.currentCurrencyCode ?? pricing?.defaultCurrencyCode ?? 'CZK';
    const currentMinimumFractionDigits = pricing?.minimumFractionDigits ?? 0;
    const availableCurrencies = pricing?.availableCurrencies;

    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) => {
        const effectiveCurrencyCode = options?.currencyCode ?? currentCurrencyCode;
        const minimumFractionDigits =
            effectiveCurrencyCode === currentCurrencyCode
                ? currentMinimumFractionDigits
                : availableCurrencies?.find((currency) => currency.code === effectiveCurrencyCode)?.minFractionDigits;

        return formatPrice(
            getPriceAsFloat(price),
            effectiveCurrencyCode,
            t,
            defaultLocale,
            minimumFractionDigits,
            options,
        );
    };
};
