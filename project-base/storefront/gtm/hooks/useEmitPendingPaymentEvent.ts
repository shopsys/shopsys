import { getGtmPaymentEvent } from 'gtm/factories/getGtmPaymentEvent';
import { buildPaymentAttemptKey, canEmitPaymentEvent, markPaymentEventEmitted } from 'gtm/utils/gtmPaymentEventDedup';
import {
    getGtmPendingPaymentFromLocalStorage,
    removeGtmPendingPaymentFromLocalStorage,
} from 'gtm/utils/gtmPaymentEventLocalStorage';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useRef } from 'react';
import { removeGoPayPaymentSession } from 'utils/goPayPaymentSessionStorage';

type OrderPaymentData = {
    orderUuid: string;
    isPaid: boolean;
    hasPaymentInProcess: boolean;
    paymentTransactionsCount: number;
    paymentName: string;
    orderNumber: string;
};

/**
 * Shared hook for emitting ec.payment GTM event from pending payment data.
 * Used by: /order-payment-confirmation (primary), /order-confirmation (fallback), order detail pages (after retry reload).
 *
 * Emits once per payment attempt for any payment state reached on a confirmation page.
 * For `InProcess`, `isPaymentSuccessful` is set to `true` optimistically — pending bank transfers
 * may never be confirmed asynchronously (GoPay can't distinguish an abandoned manual transfer from
 * a completed one), and 3DS processing can also sit in InProcess before resolving. The first-seen
 * state wins: if a later recheck flips InProcess to Failed, we do not re-emit — the optimistic
 * `true` is an accepted inaccuracy per the product decision.
 * Deduplicates via sessionStorage to prevent double emission across page navigations.
 */
export const useEmitPendingPaymentEvent = () => {
    const hasFiredRef = useRef(false);

    const tryEmitPaymentEvent = (orderPaymentData: OrderPaymentData): void => {
        if (hasFiredRef.current) {
            return;
        }

        const { orderUuid, isPaid, hasPaymentInProcess, paymentTransactionsCount, paymentName, orderNumber } =
            orderPaymentData;

        const pendingPayment = getGtmPendingPaymentFromLocalStorage();
        if (!pendingPayment || pendingPayment.orderUuid !== orderUuid) {
            return;
        }

        // Pending payment stores the expected paymentTransactionsCount AFTER the attempt.
        // If the order data shows a lower count, the query result is stale — skip emission
        // and wait for a re-render with refreshed data.
        if (
            pendingPayment.paymentTransactionsCount !== undefined &&
            paymentTransactionsCount < pendingPayment.paymentTransactionsCount
        ) {
            return;
        }

        const pendingRetryCount =
            pendingPayment.paymentTransactionsCount !== undefined
                ? Math.max(0, pendingPayment.paymentTransactionsCount - 1)
                : undefined;
        const statusRetryCount = Math.max(0, paymentTransactionsCount - 1);
        const paymentRetryCount =
            pendingRetryCount !== undefined ? Math.max(statusRetryCount, pendingRetryCount) : statusRetryCount;

        const attemptKey = buildPaymentAttemptKey({ orderUuid, paymentRetryCount });

        if (!canEmitPaymentEvent('final', attemptKey)) {
            removeGtmPendingPaymentFromLocalStorage();
            removeGoPayPaymentSession();
            hasFiredRef.current = true;

            return;
        }

        const isPaymentSuccessful = isPaid || hasPaymentInProcess;
        const gtmPaymentEvent = getGtmPaymentEvent(orderNumber, paymentName, isPaymentSuccessful, paymentRetryCount);
        gtmSafePushEvent(gtmPaymentEvent);
        markPaymentEventEmitted('final', attemptKey);

        removeGtmPendingPaymentFromLocalStorage();
        removeGoPayPaymentSession();
        hasFiredRef.current = true;
    };

    return { tryEmitPaymentEvent };
};
