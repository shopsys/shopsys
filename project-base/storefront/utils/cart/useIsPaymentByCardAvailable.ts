import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';

export const useIsPaymentByCardAvailable = (paymentTransactionCount: number) => {
    const [{ data: settingsData }] = useSettingsQuery();
    const maxAllowedPaymentTransactions =
        settingsData?.settings?.maxAllowedPaymentTransactions ?? Number.MAX_SAFE_INTEGER;
    const isPaymentWithCardAvailable = paymentTransactionCount < maxAllowedPaymentTransactions;

    return isPaymentWithCardAvailable;
};
