'use client';

import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettings } from 'components/providers/SettingsProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { formatPrice } from 'utils/formaters/formatPrice';

export type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const { t } = useTranslation();
    const { pricing } = useSettings();
    const { defaultLocale = 'en' } = useDomainConfig();

    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = pricing;
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
