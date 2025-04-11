import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import {
    getPaymentSessionExpiredErrorMessage,
    useUpdatePaymentStatus,
} from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { OrderConfirmationProducts } from 'components/Pages/OrderConfirmation/OrderConfirmationProducts';
import { OrderConfirmationStepper } from 'components/Pages/OrderConfirmation/OrderConfirmationStepper';
import { FlowTypesEnum } from 'components/Pages/OrderConfirmation/OrderConfirmationStepperFlows';
import { OrderConfirmationSummary } from 'components/Pages/OrderConfirmation/OrderConfirmationSummary';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { useOrderPaymentFailedContentQuery } from 'graphql/requests/orders/queries/OrderPaymentFailedContentQuery.generated';
import { useOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';
import { TypeCustomerUserRoleEnum, TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { PaymentTypeEnum } from 'types/payment';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const { orderIdentifier, orderPaymentStatusPageValidityHash, orderEmail, orderUrlHash } = useRouter().query;
    const orderUuid = getStringFromUrlQuery(orderIdentifier);
    const orderPaymentStatusPageValidityHashParam = getStringFromUrlQuery(orderPaymentStatusPageValidityHash);
    const paymentStatusData = useUpdatePaymentStatus(orderUuid, orderPaymentStatusPageValidityHashParam);

    const [
        { data: failedContentData, fetching: isOrderPaymentFailedContentFetching, error: isOrderPaymentFailedError },
    ] = useOrderPaymentFailedContentQuery({
        variables: { orderUuid },
        pause: !paymentStatusData || paymentStatusData.UpdatePaymentStatus.isPaid,
    });
    const [{ data: successContentData, fetching: isOrderPaymentSuccessfulContentFetching }] =
        useOrderPaymentSuccessfulContentQuery({
            variables: { orderUuid },
            pause: !paymentStatusData || !paymentStatusData.UpdatePaymentStatus.isPaid,
        });

    const paymentSessionExpiredErrorMessage = getPaymentSessionExpiredErrorMessage(isOrderPaymentFailedError, t);

    const [{ data: orderData }] = useOrderDetailByHashQuery({
        variables: { urlHash: orderUrlHash as string },
        pause: !orderUrlHash,
    });

    if (paymentSessionExpiredErrorMessage) {
        return (
            <CommonLayout
                pageTypeOverride="order-confirmation"
                title={t('Order sent')}
                isFetchingData={
                    !paymentStatusData || isOrderPaymentFailedContentFetching || isOrderPaymentSuccessfulContentFetching
                }
            >
                <Webline>
                    <ConfirmationPageContent
                        content={paymentSessionExpiredErrorMessage}
                        heading={t('Your payment session expired')}
                        headingClassName="text-textError"
                    />
                </Webline>
            </CommonLayout>
        );
    }

    const isFetchingData =
        !paymentStatusData || isOrderPaymentFailedContentFetching || isOrderPaymentSuccessfulContentFetching;

    if (!orderData?.order) {
        return null;
    }

    const orderPayment = orderData.order.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment);
    const orderTransport = orderData.order.items.find((item) => item.type === TypeOrderItemTypeEnum.Transport);

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={t('Order sent')}>
                <Webline>
                    <PaymentStatus
                        failedContentData={failedContentData}
                        paymentStatusData={paymentStatusData}
                        successContentData={successContentData}
                    />

                    <OrderConfirmationStepper
                        flow={
                            successContentData && paymentStatusData?.UpdatePaymentStatus.isPaid
                                ? FlowTypesEnum.PaymentSuccess
                                : FlowTypesEnum.PaymentFailed
                        }
                    />

                    <div className="vl:grid-cols-3 vl:gap-10 grid gap-4">
                        <div className="vl:col-span-2 vl:flex-col flex flex-col-reverse gap-4">
                            {failedContentData &&
                                paymentStatusData?.UpdatePaymentStatus.payment.type === PaymentTypeEnum.GoPay && (
                                    <PaymentsInOrderSelect
                                        orderUuid={orderUuid}
                                        paymentTransactionCount={
                                            paymentStatusData.UpdatePaymentStatus.paymentTransactionsCount
                                        }
                                    />
                                )}

                            {successContentData && paymentStatusData?.UpdatePaymentStatus.isPaid && (
                                <>
                                    <OrderCustomerInfo order={orderData.order} />

                                    <RegistrationAfterOrder
                                        orderEmail={orderEmail as string | undefined}
                                        orderUrlHash={orderUrlHash as string | undefined}
                                        orderUuid={orderUuid}
                                    />
                                </>
                            )}
                        </div>

                        <div className="vl:col-span-1 flex flex-1 flex-col gap-2.5">
                            <OrderConfirmationProducts items={orderData.order.items} />

                            <OrderConfirmationSummary
                                promoCode={orderData.order.promoCode}
                                totalPrice={orderData.order.totalPrice}
                                payment={{
                                    name: orderPayment?.name ?? orderData.order.payment.name,
                                    price:
                                        orderPayment?.totalPrice.priceWithVat ??
                                        orderData.order.payment.price.priceWithVat,
                                }}
                                transport={{
                                    name: orderTransport?.name ?? orderData.order.transport.name,
                                    price:
                                        orderTransport?.totalPrice.priceWithVat ??
                                        orderData.order.transport.price.priceWithVat,
                                }}
                            />
                        </div>
                    </div>
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const orderUuid = getStringFromUrlQuery(context.query.orderIdentifier);

    if (orderUuid === '') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    });
});

export default OrderPaymentConfirmationPage;
