import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import { OrderConfirmationProducts } from 'components/Pages/OrderConfirmation/OrderConfirmationProducts';
import { OrderConfirmationStepper } from 'components/Pages/OrderConfirmation/OrderConfirmationStepper';
import { OrderConfirmationSummary } from 'components/Pages/OrderConfirmation/OrderConfirmationSummary';
import {
    getOrderConfirmationPaymentView,
    getOrderConfirmationSummaryItems,
    useOrderConfirmationOrder,
    useOrderConfirmationPageContext,
} from 'components/Pages/OrderConfirmation/orderConfirmationPageUtils';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useEffect, useEffectEvent } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { fetchCart } = useCurrentCart(false);
    const { isReturnHashFetching, orderConfirmationPageContext, orderUrlHash, returnHash } =
        useOrderConfirmationPageContext(url);
    const { hasPaymentStatusUpdateError, isOrderFetching, isWaitingForPaymentStatusUpdate, order } =
        useOrderConfirmationOrder(orderConfirmationPageContext, orderUrlHash, returnHash);
    const [orderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: order?.urlHash }],
        url,
    );

    const onFetchCart = useEffectEvent(() => {
        fetchCart();
    });

    useEffect(() => {
        onFetchCart();
    }, []);

    const paymentView = order ? getOrderConfirmationPaymentView(order, orderConfirmationPageContext) : null;
    const gtmPageType = getOrderConfirmationGtmPageType(order, paymentView);
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(gtmPageType);
    const isInvalidPaymentReturn = orderConfirmationPageContext.type === 'invalidPaymentReturn';
    const isOrderConfirmationPageFetching =
        !isInvalidPaymentReturn &&
        (isOrderFetching || isReturnHashFetching || !order || !paymentView || isWaitingForPaymentStatusUpdate);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent, isOrderConfirmationPageFetching);

    if (isInvalidPaymentReturn) {
        return (
            <>
                <MetaRobots content="noindex" />

                <CommonLayout
                    isFetchingData={false}
                    pageTypeOverride="order-confirmation"
                    title={t('Order confirmation')}
                >
                    <Webline>
                        <ConfirmationPageContent
                            content={t('Please check your order confirmation e-mail or sign in to your account.')}
                            heading={t('Order confirmation can no longer be displayed.')}
                            headingIcon={WarningIcon}
                            headingVariant="error"
                        />
                    </Webline>
                </CommonLayout>
            </>
        );
    }

    if (!order) {
        return <OrderConfirmationLoadingState title={t('Thank you for your order')} />;
    }

    if (!paymentView) {
        return <OrderConfirmationLoadingState title={t('Thank you for your order')} />;
    }

    if (isWaitingForPaymentStatusUpdate) {
        return <OrderConfirmationLoadingState title={t('Thank you for your order')} />;
    }

    const { isPaymentFailed, isPaymentInProcess, isPaymentSuccessful, shouldShowPaymentGateway, stepperFlow } =
        paymentView;
    const { orderPayment, orderRounding, orderTransport } = getOrderConfirmationSummaryItems(order);

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout
                isFetchingData={isOrderFetching || isReturnHashFetching}
                pageTypeOverride="order-confirmation"
                title={t('Thank you for your order')}
            >
                <Webline tid={TIDs.pages_orderconfirmation}>
                    {hasPaymentStatusUpdateError ? (
                        <ConfirmationPageContent
                            content={t(
                                'Your order was created, but we could not update the current payment status. Please check your order confirmation e-mail or sign in to your account.',
                            )}
                            heading={t('Payment status could not be verified.')}
                            headingIcon={WarningIcon}
                            headingVariant="error"
                            orderDetailUrl={orderDetailUrl}
                        />
                    ) : shouldShowPaymentGateway ? (
                        <ConfirmationPageContent
                            heading={t('Your order was created')}
                            headingDescription={t('You are being redirected to the payment gateway.')}
                            headingIcon={SpinnerIcon}
                            headingVariant="info"
                        />
                    ) : order.hasExternalPayment ? (
                        <PaymentStatus order={order} />
                    ) : (
                        <ConfirmationPageContent
                            content={order.confirmationPageContent.content}
                            heading={t('Your order was created')}
                            headingIcon={CheckmarkIcon}
                            headingVariant="success"
                            orderDetailUrl={orderDetailUrl}
                        />
                    )}

                    <OrderConfirmationStepper flow={stepperFlow} />

                    <div className="grid vl:grid-cols-3 gap-4 vl:gap-10">
                        <div className="vl:col-span-2 flex vl:flex-col flex-col-reverse gap-4">
                            {shouldShowPaymentGateway && (
                                <div className="mt-4">
                                    <GoPayGateway orderUrlHash={order.urlHash} orderUuid={order.uuid} />
                                </div>
                            )}

                            {!shouldShowPaymentGateway && isPaymentFailed && order.hasExternalPayment && (
                                <PaymentsInOrderSelect orderUrlHash={order.urlHash} orderUuid={order.uuid} />
                            )}

                            {(!order.hasExternalPayment ||
                                hasPaymentStatusUpdateError ||
                                isPaymentInProcess ||
                                isPaymentSuccessful) && <OrderCustomerInfo order={order} />}

                            {(!order.hasExternalPayment || isPaymentSuccessful) && (
                                <RegistrationAfterOrder
                                    companyNumber={order.companyNumber}
                                    orderEmail={order.email}
                                    orderUrlHash={order.urlHash}
                                    orderUuid={order.uuid}
                                />
                            )}
                        </div>

                        <div className="vl:col-span-1 flex flex-1 flex-col gap-2.5">
                            <OrderConfirmationProducts items={order.items} />

                            <OrderConfirmationSummary
                                promoCode={order.promoCode}
                                roundingPrice={orderRounding?.totalPrice}
                                totalPrice={order.totalPrice}
                                payment={{
                                    name: orderPayment?.name,
                                    price: orderPayment?.totalPrice.priceWithVat,
                                }}
                                transport={{
                                    name: orderTransport?.name,
                                    price: orderTransport?.totalPrice.priceWithVat,
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
    return initServerSideProps({
        context,
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderConfirmationPage;

const OrderConfirmationLoadingState: FC<{ title: string }> = ({ title }) => (
    <>
        <MetaRobots content="noindex" />

        <CommonLayout isFetchingData pageTypeOverride="order-confirmation" title={title}>
            <Webline tid={TIDs.pages_orderconfirmation} />
        </CommonLayout>
    </>
);

const getOrderConfirmationGtmPageType = (
    order: ReturnType<typeof useOrderConfirmationOrder>['order'],
    paymentView: ReturnType<typeof getOrderConfirmationPaymentView> | null,
): GtmPageType => {
    if (!order?.hasExternalPayment || !paymentView) {
        return GtmPageType.order_confirmation;
    }

    if (paymentView.shouldShowPaymentGateway) {
        return GtmPageType.order_confirmation;
    }

    if (order.isPaid && paymentView.isPaymentSuccessful) {
        return GtmPageType.payment_success;
    }

    if (order.hasPaymentInProcess && paymentView.isPaymentInProcess) {
        return GtmPageType.payment_in_process;
    }

    if (paymentView.isPaymentFailed) {
        return GtmPageType.payment_fail;
    }

    return GtmPageType.order_confirmation;
};
