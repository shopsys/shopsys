import {
    TypeUpdatePaymentStatusMutation,
    useUpdatePaymentStatusMutation,
} from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { TypePaymentContentPageStatusEnum } from 'graphql/types';
import { useEmitPendingPaymentEvent } from 'gtm/hooks/useEmitPendingPaymentEvent';
import {
    getGtmCreateOrderEventFromLocalStorage,
    removeGtmCreateOrderEventFromLocalStorage,
} from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import { Translate } from 'next-translate';
import { useCallback, useEffect, useRef, useState } from 'react';
import { CombinedError } from 'urql';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { getOrderPaymentItem } from 'utils/mappers/order';

const INPROCESS_RETRY_DELAY_MS = process.env.NODE_ENV === 'test' ? 10 : 3000;

export const getPaymentSessionExpiredErrorMessage = (
    t: Translate,
    ...combinedErrors: (CombinedError | undefined)[]
): string => {
    for (const error of combinedErrors) {
        if (!error?.graphQLErrors.length) {
            continue;
        }

        const { applicationError } = getUserFriendlyErrors(error, t);
        if (applicationError?.type === 'order-sent-page-not-available') {
            return t('Order sent page is not available.');
        }
    }

    return '';
};

type UseUpdatePaymentStatusResult = {
    paymentStatusData: TypeUpdatePaymentStatusMutation | undefined;
    statusError: boolean;
    isCheckingStatus: boolean;
    recheckPaymentStatus: () => Promise<RecheckedPaymentStatus>;
};

type RecheckedPaymentStatus = TypePaymentContentPageStatusEnum | 'error';

/**
 * Fetches payment status from backend (calls GoPay API internally).
 * - 1st call: on mount
 * - If InProcess: 1 automatic retry after 3s (covers 3DS processing delay)
 * - After that: manual "Check status" button via recheckPaymentStatus()
 * - Emits ec.payment GTM event on the first resolved state, including InProcess
 *   (emitted optimistically with isPaymentSuccessful=true — see useEmitPendingPaymentEvent)
 */
export const useUpdatePaymentStatus = (
    orderUuid: string,
    orderPaymentStatusPageValidityHash: string | null,
): UseUpdatePaymentStatusResult => {
    const [, updatePaymentStatusMutation] = useUpdatePaymentStatusMutation();
    const [paymentStatusData, setPaymentStatusData] = useState<TypeUpdatePaymentStatusMutation>();
    const [statusError, setStatusError] = useState(false);
    const [isCheckingStatus, setIsCheckingStatus] = useState(false);
    const hasAutoRetriedRef = useRef(false);
    const { tryEmitPaymentEvent } = useEmitPendingPaymentEvent();

    const fetchStatus = useCallback(async (): Promise<RecheckedPaymentStatus> => {
        if (!orderUuid) {
            setIsCheckingStatus(false);
            setStatusError(true);

            return 'error';
        }

        setIsCheckingStatus(true);
        setStatusError(false);

        const result = await updatePaymentStatusMutation({
            orderUuid,
            orderPaymentStatusPageValidityHash,
        });

        setIsCheckingStatus(false);

        const paymentStatus = result.data?.UpdatePaymentStatus;
        if (!paymentStatus) {
            setStatusError(true);

            return 'error';
        }

        setPaymentStatusData({ UpdatePaymentStatus: paymentStatus });

        // Clean up order creation event after first successful status check
        const { gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart } = getGtmCreateOrderEventFromLocalStorage();
        if (gtmCreateOrderEventOrderPart && gtmCreateOrderEventUserPart) {
            removeGtmCreateOrderEventFromLocalStorage();
        }

        // Emit ec.payment for the first resolved state (InProcess emits optimistically)
        const paymentItem = getOrderPaymentItem(paymentStatus.items);
        tryEmitPaymentEvent({
            orderUuid,
            isPaid: paymentStatus.isPaid,
            hasPaymentInProcess: paymentStatus.hasPaymentInProcess,
            paymentTransactionsCount: paymentStatus.paymentTransactionsCount,
            paymentName: paymentItem?.payment?.name ?? '',
            orderNumber: paymentStatus.number,
        });

        // One automatic retry if still InProcess (covers 3DS processing delay)
        if (paymentStatus.hasPaymentInProcess && !paymentStatus.isPaid && !hasAutoRetriedRef.current) {
            hasAutoRetriedRef.current = true;
            setIsCheckingStatus(true);

            setTimeout(() => {
                void updatePaymentStatusMutation({
                    orderUuid,
                    orderPaymentStatusPageValidityHash,
                })
                    .then((retryResult) => {
                        const retryStatus = retryResult.data?.UpdatePaymentStatus;
                        if (!retryStatus) {
                            return;
                        }

                        setPaymentStatusData({ UpdatePaymentStatus: retryStatus });

                        // Usually a no-op: the first-fetch tryEmitPaymentEvent above already emitted and set
                        // the hook's internal hasFiredRef. Kept as a fallback for the race where the first
                        // fetch returned with a stale paymentTransactionsCount (hook's stale-data guard then
                        // skipped emission); the 3s retry typically comes back with a fresh count and emits.
                        const retryPaymentItem = getOrderPaymentItem(retryStatus.items);
                        tryEmitPaymentEvent({
                            orderUuid,
                            isPaid: retryStatus.isPaid,
                            hasPaymentInProcess: retryStatus.hasPaymentInProcess,
                            paymentTransactionsCount: retryStatus.paymentTransactionsCount,
                            paymentName: retryPaymentItem?.payment?.name ?? '',
                            orderNumber: retryStatus.number,
                        });
                    })
                    .finally(() => {
                        setIsCheckingStatus(false);
                    });
            }, INPROCESS_RETRY_DELAY_MS);
        }

        return paymentStatus.paymentPageContent?.status ?? resolvePaymentPageStatus(paymentStatus);
    }, [orderUuid, orderPaymentStatusPageValidityHash, updatePaymentStatusMutation, tryEmitPaymentEvent]);

    // Initial fetch on mount
    useEffect(() => {
        void fetchStatus();
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    // Manual recheck (for "Check payment status" button)
    const recheckPaymentStatus = useCallback(() => {
        return fetchStatus();
    }, [fetchStatus]);

    return { paymentStatusData, statusError, isCheckingStatus, recheckPaymentStatus };
};

export const resolvePaymentPageStatus = (
    paymentStatusOrder: TypeUpdatePaymentStatusMutation['UpdatePaymentStatus'] | undefined,
): TypePaymentContentPageStatusEnum => {
    if (paymentStatusOrder?.isPaid === true) {
        return TypePaymentContentPageStatusEnum.Successful;
    }

    if (paymentStatusOrder?.hasPaymentInProcess === true) {
        return TypePaymentContentPageStatusEnum.InProcess;
    }

    return TypePaymentContentPageStatusEnum.Failed;
};
