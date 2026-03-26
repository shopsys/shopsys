import { isClient } from 'utils/isClient';

const GTM_PENDING_PAYMENT_LOCAL_STORAGE_KEY = 'gtmPendingPayment' as const;
const normalizeDomainUrl = (domainUrl?: string) => domainUrl?.replace(/\/+$/, '');

type GtmPendingPaymentType = {
    orderUuid: string;
    orderNumber: string;
    paymentName: string;
    paymentTransactionsCount?: number;
    domainUrl: string;
    timestamp: number;
};

export const saveGtmPendingPaymentInLocalStorage = (pendingPayment: Omit<GtmPendingPaymentType, 'timestamp'>): void => {
    if (!isClient) {
        return;
    }

    const data: GtmPendingPaymentType = {
        ...pendingPayment,
        domainUrl: normalizeDomainUrl(pendingPayment.domainUrl) ?? pendingPayment.domainUrl,
        timestamp: Date.now(),
    };

    try {
        localStorage.setItem(GTM_PENDING_PAYMENT_LOCAL_STORAGE_KEY, JSON.stringify(data));
    } catch {
        // Ignore storage errors, pending payment recovery is best-effort only.
    }
};

export const getGtmPendingPaymentFromLocalStorage = (currentDomainUrl?: string): GtmPendingPaymentType | null => {
    if (!isClient) {
        return null;
    }

    const stringifiedData = localStorage.getItem(GTM_PENDING_PAYMENT_LOCAL_STORAGE_KEY);

    if (stringifiedData === null) {
        return null;
    }

    try {
        const data = JSON.parse(stringifiedData) as GtmPendingPaymentType;

        if (currentDomainUrl && normalizeDomainUrl(data.domainUrl) !== normalizeDomainUrl(currentDomainUrl)) {
            return null;
        }

        return data;
    } catch {
        localStorage.removeItem(GTM_PENDING_PAYMENT_LOCAL_STORAGE_KEY);

        return null;
    }
};

export const removeGtmPendingPaymentFromLocalStorage = (): void => {
    if (!isClient) {
        return;
    }
    localStorage.removeItem(GTM_PENDING_PAYMENT_LOCAL_STORAGE_KEY);
};
