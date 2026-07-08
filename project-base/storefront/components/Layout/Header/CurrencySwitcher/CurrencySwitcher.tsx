import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { twJoin } from 'tailwind-merge';
import { setCurrencyCodeToCookies } from 'utils/currency/currencyCookie';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export const CurrencySwitcher: FC = () => {
    const { t } = useTranslation();
    const domainConfig = useDomainConfig();
    const router = useRouter();
    const [{ data: settingsData }] = useSettingsQuery();

    const pricing = settingsData?.settings?.pricing;
    const availableCurrencies = pricing?.availableCurrencies ?? [];
    const currentCurrencyCode = pricing?.currentCurrencyCode;

    if (availableCurrencies.length <= 1 || currentCurrencyCode === undefined) {
        return null;
    }

    const switchCurrency = (newCurrencyCode: string) => {
        if (newCurrencyCode === currentCurrencyCode) {
            return;
        }

        setCurrencyCodeToCookies(newCurrencyCode, domainConfig);
        dispatchBroadcastChannel('reloadPage', domainConfig.domainId);
        router.reload();
    };

    return (
        <div className="flex items-center gap-1" data-tid={TIDs.header_currency_switcher}>
            {availableCurrencies.map((currency) => (
                <button
                    key={currency.code}
                    aria-label={t('Switch currency to {{ currencyCode }}', { currencyCode: currency.code })}
                    data-tid={TIDs.header_currency_switcher_option_ + currency.code}
                    title={currency.name}
                    type="button"
                    className={twJoin(
                        'rounded-lg border px-2 py-1 font-bold text-sm transition',
                        currency.code === currentCurrencyCode
                            ? 'cursor-default bg-background-more text-text-default'
                            : 'cursor-pointer border-transparent text-text-default opacity-60 hover:opacity-100',
                    )}
                    onClick={() => switchCurrency(currency.code)}
                >
                    {currency.code}
                </button>
            ))}
        </div>
    );
};
