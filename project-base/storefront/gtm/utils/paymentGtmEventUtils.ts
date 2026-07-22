import { TypeOrderConfirmationPageContentStatusEnum } from 'graphql/types';
import { getGtmPaymentEvent } from 'gtm/factories/getGtmPaymentEvent';
import { GtmPaymentEventType } from 'gtm/types/events';

type GtmPaymentEventPaymentStatusUpdate = {
    orderNumber: string;
    paymentName: string;
    lastPaymentStatus: string | null;
    paymentTransactionsCount: number;
    confirmationPageContent: {
        status: TypeOrderConfirmationPageContentStatusEnum;
    };
};

export const getGtmPaymentRetryCount = (paymentTransactionsCount: number): number =>
    Math.max(paymentTransactionsCount - 1, 0);

export const getIsPaymentSuccessfulByConfirmationStatus = (
    status: TypeOrderConfirmationPageContentStatusEnum,
): boolean => {
    switch (status) {
        case TypeOrderConfirmationPageContentStatusEnum.Successful:
        case TypeOrderConfirmationPageContentStatusEnum.InProcess:
            return true;
        case TypeOrderConfirmationPageContentStatusEnum.Failed:
            return false;
    }

    throw new Error(`Unknown order confirmation page content status "${status}".`);
};

export const getGtmPaymentEventFromPaymentStatusUpdate = (
    paymentStatusUpdate: GtmPaymentEventPaymentStatusUpdate,
): GtmPaymentEventType =>
    getGtmPaymentEvent(
        paymentStatusUpdate.orderNumber,
        paymentStatusUpdate.paymentName,
        getIsPaymentSuccessfulByConfirmationStatus(paymentStatusUpdate.confirmationPageContent.status),
        getGtmPaymentRetryCount(paymentStatusUpdate.paymentTransactionsCount),
        paymentStatusUpdate.lastPaymentStatus,
    );
