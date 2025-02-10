'use client';

import { useAppConfig } from 'components/providers/AppConfigProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { formatPrice } from 'utils/formaters/formatPrice';

type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const { t } = useTranslation();
    const { pricing } = useAppConfig((settings) => settings.settings);
    const { defaultLocale = 'en' } = useDomainConfig();

    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = pricing;
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
