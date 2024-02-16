import { useSettingsQueryApi } from 'graphql/generated';

export const useIsPaymentByCardAvailable = (paymentTransactionCount: number) => {
    const [{ data }] = useSettingsQueryApi();
    const maxAllowedPaymentTransactions = data?.settings?.maxAllowedPaymentTransactions ?? Number.MAX_SAFE_INTEGER;
    const isPaymentWithCardAvailable = paymentTransactionCount < maxAllowedPaymentTransactions;

    return isPaymentWithCardAvailable;
};
