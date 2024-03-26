import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';

export const useIsPaymentByCardAvailable = (paymentTransactionCount: number) => {
    const [{ data }] = useSettingsQuery();
    const maxAllowedPaymentTransactions = data?.settings?.maxAllowedPaymentTransactions ?? Number.MAX_SAFE_INTEGER;
    const isPaymentWithCardAvailable = paymentTransactionCount < maxAllowedPaymentTransactions;

    return isPaymentWithCardAvailable;
};
