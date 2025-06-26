'use client';

import { useAppConfig } from 'components/providers/AppConfigProvider';
import { formatPrice } from 'utils/formaters/formatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const { t } = useTranslation();
    const { pricing } = useAppConfig((settings) => settings.settings);
    const { defaultLocale = 'en' } = useAppConfig((appConfig) => appConfig.domainConfig);

    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = pricing;
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
