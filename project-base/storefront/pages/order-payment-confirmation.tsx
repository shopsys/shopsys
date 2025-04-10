import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import { ShowPaymentInstructionButton } from 'components/Pages/Order/PaymentConfirmation/ShowPaymentInstructionButton';
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
import { useOrderPaymentPageContentQuery } from 'graphql/requests/orders/queries/OrderPaymentPageContentQuery.generated';
import { TypeCustomerUserRoleEnum, TypeOrderItemTypeEnum, TypePaymentContentPageStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

export type OrderPaymentConfirmationUrlQuery = {
    orderIdentifier: string | undefined;
    orderEmail: string | undefined;
    orderUrlHash?: string | undefined;
    orderPaymentStatusPageValidityHash: string | undefined;
};

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const { orderIdentifier, orderEmail, orderUrlHash, orderPaymentStatusPageValidityHash } = useRouter()
        .query as OrderPaymentConfirmationUrlQuery;
    const orderUuid = getStringFromUrlQuery(orderIdentifier);
    const urlHash = getStringFromUrlQuery(orderUrlHash);
    const orderPaymentStatusPageValidityHashParam = getStringFromUrlQuery(orderPaymentStatusPageValidityHash);

    const paymentStatusData = useUpdatePaymentStatus(orderUuid!, orderPaymentStatusPageValidityHashParam);

    const [{ data: orderData, fetching: isOrderFetching }] = useOrderDetailByHashQuery({
        variables: { urlHash },
        pause: !urlHash || !paymentStatusData,
    });

    const order = orderData?.order;

    const [
        {
            data: orderPaymentPageContentData,
            fetching: isOrderPaymentPageContentFetching,
            error: isOrderPaymentPageContentError,
        },
    ] = useOrderPaymentPageContentQuery({
        variables: { orderUuid: orderUuid! },
        pause: !paymentStatusData,
    });

    const orderPaymentPageStatus = orderPaymentPageContentData?.orderPaymentPageContent.status;

    const paymentSessionExpiredErrorMessage = getPaymentSessionExpiredErrorMessage(t, isOrderPaymentPageContentError);

    const isFetchingData = isOrderFetching || isOrderPaymentPageContentFetching;

    if (paymentSessionExpiredErrorMessage) {
        return (
            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={t('Order sent')}>
                <Webline>
                    <ConfirmationPageContent
                        content={paymentSessionExpiredErrorMessage}
                        heading={t('Your payment session expired')}
                        headingClassName="text-text-error"
                    />
                </Webline>
            </CommonLayout>
        );
    }

    if (!order) {
        return null;
    }

    const orderPayment = order.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment);
    const orderTransport = order.items.find((item) => item.type === TypeOrderItemTypeEnum.Transport);

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={t('Order sent')}>
                <Webline>
                    <PaymentStatus orderData={orderData} orderPaymentPageContentData={orderPaymentPageContentData} />

                    {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.InProcess &&
                        order.lastExternalPaymentUrl && (
                            <div className="mt-4">
                                <ShowPaymentInstructionButton
                                    href={order.lastExternalPaymentUrl}
                                    orderPaymentStatusPageValidityHash={orderPaymentStatusPageValidityHashParam}
                                    orderUuid={orderUuid}
                                />
                            </div>
                        )}

                    <OrderConfirmationStepper
                        flow={
                            order.hasPaymentInProcess
                                ? FlowTypesEnum.PaymentInProcess
                                : orderPaymentPageStatus === TypePaymentContentPageStatusEnum.Successful && order.isPaid
                                  ? FlowTypesEnum.PaymentSuccess
                                  : FlowTypesEnum.PaymentFailed
                        }
                    />

                    <div className="vl:grid-cols-3 vl:gap-10 grid gap-4">
                        <div className="vl:col-span-2 vl:flex-col flex flex-col-reverse gap-4">
                            {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.Failed &&
                                order.hasExternalPayment && <PaymentsInOrderSelect orderUuid={orderUuid} />}

                            {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.InProcess && (
                                <OrderCustomerInfo order={order} />
                            )}

                            {orderPaymentPageStatus === TypePaymentContentPageStatusEnum.Successful && (
                                <>
                                    <OrderCustomerInfo order={order} />

                                    <RegistrationAfterOrder
                                        orderEmail={orderEmail as string | undefined}
                                        orderUrlHash={orderUrlHash as string | undefined}
                                        orderUuid={orderUuid}
                                    />
                                </>
                            )}
                        </div>

                        <div className="vl:col-span-1 flex flex-1 flex-col gap-2.5">
                            <OrderConfirmationProducts items={order.items} />

                            <OrderConfirmationSummary
                                promoCode={order.promoCode}
                                totalPrice={order.totalPrice}
                                payment={{
                                    name: orderPayment?.name ?? order.payment.name,
                                    price: orderPayment?.totalPrice.priceWithVat ?? order.payment.price.priceWithVat,
                                }}
                                transport={{
                                    name: orderTransport?.name ?? order.transport.name,
                                    price:
                                        orderTransport?.totalPrice.priceWithVat ?? order.transport.price.priceWithVat,
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
