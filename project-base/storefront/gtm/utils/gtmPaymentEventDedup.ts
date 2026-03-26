import { isClient } from 'utils/isClient';

const GTM_PAYMENT_EVENT_DEDUP_SESSION_STORAGE_KEY_PREFIX = 'gtmPaymentEventDedup' as const;

type PaymentEventKind = 'final';

type PaymentAttemptKeyInput = {
    orderUuid: string;
    paymentRetryCount: number;
    paymentName?: string;
};

const getStorageKey = (eventKind: PaymentEventKind, paymentAttemptKey: string): string =>
    `${GTM_PAYMENT_EVENT_DEDUP_SESSION_STORAGE_KEY_PREFIX}:${eventKind}:${paymentAttemptKey}`;

export const buildPaymentAttemptKey = ({ orderUuid, paymentRetryCount, paymentName }: PaymentAttemptKeyInput): string =>
    JSON.stringify([orderUuid, paymentRetryCount, paymentName ?? '']);

export const canEmitPaymentEvent = (eventKind: PaymentEventKind, paymentAttemptKey: string): boolean => {
    if (!isClient) {
        return true;
    }

    try {
        return sessionStorage.getItem(getStorageKey(eventKind, paymentAttemptKey)) === null;
    } catch {
        return true;
    }
};

export const markPaymentEventEmitted = (eventKind: PaymentEventKind, paymentAttemptKey: string): void => {
    if (!isClient) {
        return;
    }

    try {
        sessionStorage.setItem(getStorageKey(eventKind, paymentAttemptKey), '1');
    } catch {
        // Ignore storage errors, GTM should still work in restrictive browser modes.
    }
};
