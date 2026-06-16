import { TypeOrderConfirmationPageContentStatusEnum, TypeOrderItemTypeEnum } from 'graphql/types';
import { getGtmPaymentEvent } from 'gtm/factories/getGtmPaymentEvent';
import { GtmPaymentEventType } from 'gtm/types/events';

type GtmPaymentEventOrderItem = {
    type: TypeOrderItemTypeEnum;
    payment: {
        name: string;
    } | null;
};

type GtmPaymentEventOrder = {
    number: string;
    paymentStatus: string | null;
    paymentTransactionsCount: number;
    confirmationPageContent: {
        status: TypeOrderConfirmationPageContentStatusEnum;
    };
    items: GtmPaymentEventOrderItem[];
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

export const getGtmPaymentEventFromOrder = (order: GtmPaymentEventOrder): GtmPaymentEventType => {
    const paymentItem = order.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment);

    return getGtmPaymentEvent(
        order.number,
        paymentItem?.payment?.name ?? '',
        getIsPaymentSuccessfulByConfirmationStatus(order.confirmationPageContent.status),
        getGtmPaymentRetryCount(order.paymentTransactionsCount),
        order.paymentStatus,
    );
};
