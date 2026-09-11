import { useUpdatePaymentStatus } from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { FlowTypesEnum } from 'components/Pages/OrderConfirmation/OrderConfirmationStepperFlows';
import type { TypeUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import type { TypeOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { useOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { useOrderUrlHashByReturnHashQuery } from 'graphql/requests/orders/queries/OrderUrlHashByReturnHashQuery.generated';
import { TypeOrderConfirmationPageContentStatusEnum, TypeOrderItemTypeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import {
    clearOrderConfirmationContext,
    getValidOrderConfirmationContext,
    saveOrderConfirmationContext,
} from 'utils/order/orderConfirmationContextStorage';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type OrderConfirmationPageContext =
    | { type: 'pending' }
    | {
          type: 'ready';
          orderUrlHash: string;
          shouldUpdatePaymentStatus: boolean;
          paymentStatusUpdateTrigger: string | null;
      }
    | { type: 'invalidPaymentReturn' };

type OrderConfirmationOrder = NonNullable<TypeOrderDetailByHashQuery['order']>;
type UpdatedPaymentStatusResult = TypeUpdatePaymentStatusMutation['UpdatePaymentStatus'];

export const useOrderConfirmationPageContext = (domainUrl: string) => {
    const router = useRouter();
    const [orderConfirmationPageContext, setOrderConfirmationPageContext] = useState<OrderConfirmationPageContext>({
        type: 'pending',
    });

    const returnHash = getStringFromUrlQuery(router.query.returnHash);
    const requiresAction = getStringFromUrlQuery(router.query.requiresAction) === 'true';
    const orderUrlHash =
        orderConfirmationPageContext.type === 'ready' ? orderConfirmationPageContext.orderUrlHash : null;

    const [{ data: returnHashData, fetching: isReturnHashFetching, error: returnHashError }] =
        useOrderUrlHashByReturnHashQuery({
            variables: {
                returnHash,
            },
            pause: !router.isReady || returnHash === '',
        });

    useEffect(() => {
        if (!router.isReady || orderConfirmationPageContext.type !== 'pending' || returnHash !== '') {
            return;
        }

        const orderConfirmationContext = getValidOrderConfirmationContext(domainUrl);

        if (!orderConfirmationContext) {
            const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainUrl);
            router.replace(cartUrl);

            return;
        }

        setOrderConfirmationPageContext({
            type: 'ready',
            orderUrlHash: orderConfirmationContext.orderUrlHash,
            shouldUpdatePaymentStatus: requiresAction,
            paymentStatusUpdateTrigger: requiresAction ? 'requiresAction' : null,
        });

        if (requiresAction) {
            const [orderConfirmationUrl] = getInternationalizedStaticUrls(['/order-confirmation'], domainUrl);
            router.replace(orderConfirmationUrl, undefined, { shallow: true });
        }
    }, [domainUrl, orderConfirmationPageContext.type, requiresAction, returnHash, router]);

    useEffect(() => {
        if (!router.isReady || returnHash === '' || isReturnHashFetching) {
            return;
        }

        const orderUrlHashByReturnHash = returnHashData?.orderUrlHashByReturnHash;

        if (returnHashError || !orderUrlHashByReturnHash) {
            clearOrderConfirmationContext();
            setOrderConfirmationPageContext({ type: 'invalidPaymentReturn' });

            const [orderConfirmationUrl] = getInternationalizedStaticUrls(['/order-confirmation'], domainUrl);
            router.replace(orderConfirmationUrl, undefined, { shallow: true });

            return;
        }

        if (orderConfirmationPageContext.type !== 'pending') {
            const [orderConfirmationUrl] = getInternationalizedStaticUrls(['/order-confirmation'], domainUrl);
            router.replace(orderConfirmationUrl, undefined, { shallow: true });

            return;
        }

        saveOrderConfirmationContext(orderUrlHashByReturnHash, domainUrl);
        setOrderConfirmationPageContext({
            type: 'ready',
            orderUrlHash: orderUrlHashByReturnHash,
            shouldUpdatePaymentStatus: true,
            paymentStatusUpdateTrigger: returnHash,
        });

        const [orderConfirmationUrl] = getInternationalizedStaticUrls(['/order-confirmation'], domainUrl);
        router.replace(orderConfirmationUrl, undefined, { shallow: true });
    }, [
        domainUrl,
        isReturnHashFetching,
        orderConfirmationPageContext.type,
        returnHash,
        returnHashData,
        returnHashError,
        router,
    ]);

    return {
        isReturnHashFetching,
        orderConfirmationPageContext,
        orderUrlHash,
        returnHash,
    };
};

export const useOrderConfirmationOrder = (
    orderConfirmationPageContext: OrderConfirmationPageContext,
    orderUrlHash: string | null,
    returnHash: string,
) => {
    const [{ data: orderData, fetching: isOrderFetching }] = useOrderDetailByHashQuery({
        variables: {
            urlHash: orderUrlHash,
        },
        pause: orderUrlHash === null || returnHash !== '',
    });

    const shouldUpdatePaymentStatus =
        orderConfirmationPageContext.type === 'ready' &&
        orderConfirmationPageContext.shouldUpdatePaymentStatus &&
        !!orderData?.order?.hasExternalPayment;
    const paymentStatusUpdate = useUpdatePaymentStatus(
        orderData?.order?.uuid,
        orderUrlHash,
        shouldUpdatePaymentStatus,
        orderConfirmationPageContext.type === 'ready' ? orderConfirmationPageContext.paymentStatusUpdateTrigger : null,
    );

    const paymentStatusUpdateResult = paymentStatusUpdate.data?.UpdatePaymentStatus;
    const hasPaymentStatusUpdateError = shouldUpdatePaymentStatus && !!paymentStatusUpdate.error;
    const isWaitingForPaymentStatusUpdate = getIsWaitingForPaymentStatusUpdate(
        shouldUpdatePaymentStatus,
        hasPaymentStatusUpdateError,
        !!paymentStatusUpdateResult,
    );

    const order = getOrderWithUpdatedPaymentStatus(orderData?.order, paymentStatusUpdateResult);

    return {
        hasPaymentStatusUpdateError,
        isOrderFetching,
        isWaitingForPaymentStatusUpdate,
        order,
    };
};

export const getIsWaitingForPaymentStatusUpdate = (
    shouldUpdatePaymentStatus: boolean,
    hasPaymentStatusUpdateError: boolean,
    hasUpdatedPaymentStatusOrder: boolean,
) => shouldUpdatePaymentStatus && !hasUpdatedPaymentStatusOrder && !hasPaymentStatusUpdateError;

const getOrderWithUpdatedPaymentStatus = (
    order: OrderConfirmationOrder | null | undefined,
    paymentStatusUpdateResult: UpdatedPaymentStatusResult | undefined,
) => {
    if (!order) {
        return null;
    }

    if (!paymentStatusUpdateResult) {
        return order;
    }

    return {
        ...order,
        confirmationPageContent: paymentStatusUpdateResult.confirmationPageContent,
        hasPaymentInProcess: paymentStatusUpdateResult.hasPaymentInProcess,
        isAwaitingPayment: paymentStatusUpdateResult.isAwaitingPayment,
        isPaid: paymentStatusUpdateResult.isPaid,
        lastExternalPaymentUrl: paymentStatusUpdateResult.lastExternalPaymentUrl,
        paymentStatus: paymentStatusUpdateResult.lastPaymentStatus,
        paymentTransactionsCount: paymentStatusUpdateResult.paymentTransactionsCount,
    };
};

export const getOrderConfirmationPaymentView = (
    order: OrderConfirmationOrder,
    orderConfirmationPageContext: OrderConfirmationPageContext,
) => {
    const confirmationPageContentStatus = order.confirmationPageContent.status;
    const isPaymentSuccessful = confirmationPageContentStatus === TypeOrderConfirmationPageContentStatusEnum.Successful;
    const isPaymentInProcess = confirmationPageContentStatus === TypeOrderConfirmationPageContentStatusEnum.InProcess;
    const isPaymentFailed = confirmationPageContentStatus === TypeOrderConfirmationPageContentStatusEnum.Failed;
    const isPaymentReturn =
        orderConfirmationPageContext.type === 'ready' && orderConfirmationPageContext.shouldUpdatePaymentStatus;

    const shouldShowPaymentGateway = !isPaymentReturn && order.isAwaitingPayment;

    return {
        isPaymentFailed,
        isPaymentInProcess,
        isPaymentSuccessful,
        shouldShowPaymentGateway,
        stepperFlow: getOrderConfirmationStepperFlow(
            shouldShowPaymentGateway,
            order.hasPaymentInProcess,
            isPaymentSuccessful,
            order.isPaid,
            isPaymentFailed,
        ),
    };
};

const getOrderConfirmationStepperFlow = (
    shouldShowPaymentGateway: boolean,
    hasPaymentInProcess: boolean,
    isPaymentSuccessful: boolean,
    isPaid: boolean,
    isPaymentFailed: boolean,
) => {
    if (shouldShowPaymentGateway) {
        return FlowTypesEnum.PaymentAwaiting;
    }

    if (hasPaymentInProcess) {
        return FlowTypesEnum.PaymentInProcess;
    }

    if (isPaymentSuccessful && isPaid) {
        return FlowTypesEnum.PaymentSuccess;
    }

    if (isPaymentFailed) {
        return FlowTypesEnum.PaymentFailed;
    }

    return FlowTypesEnum.PaymentAwaiting;
};

export const getOrderConfirmationSummaryItems = (order: OrderConfirmationOrder) => ({
    orderPayment: order.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment),
    orderRounding: order.items.find((item) => item.type === TypeOrderItemTypeEnum.Rounding),
    orderTransport: order.items.find((item) => item.type === TypeOrderItemTypeEnum.Transport),
});
