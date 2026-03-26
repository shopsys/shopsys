import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { OrderConfirmationProducts } from 'components/Pages/OrderConfirmation/OrderConfirmationProducts';
import { OrderConfirmationStepper } from 'components/Pages/OrderConfirmation/OrderConfirmationStepper';
import { FlowTypesEnum } from 'components/Pages/OrderConfirmation/OrderConfirmationStepperFlows';
import { OrderConfirmationSummary } from 'components/Pages/OrderConfirmation/OrderConfirmationSummary';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useOrderDetailByHashOrUuidQuery } from 'graphql/requests/orders/queries/OrderDetailByHashOrUuidQuery.generated';
import {
    OrderSentPageContentQueryDocument,
    TypeOrderSentPageContentQueryVariables,
    useOrderSentPageContentQuery,
} from 'graphql/requests/orders/queries/OrderSentPageContentQuery.generated';
import { TypeCustomerUserRoleEnum, TypeOrderItemTypeEnum, TypePaymentTypeEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useEmitPendingPaymentEvent } from 'gtm/hooks/useEmitPendingPaymentEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useRouter } from 'next/router';
import Trans from 'next-translate/Trans';
import { useEffect, useEffectEvent, useRef, useState } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import { buildPaymentConfirmationUrlFromSession } from 'utils/goPayPaymentSessionStorage';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type OrderConfirmationUrlQuery = Partial<{
    orderUuid: string;
    companyNumber: string;
    orderEmail: string;
    orderPaymentType: TypePaymentTypeEnum;
    orderUrlHash?: string;
    orderPaymentStatusPageValidityHash: string;
    requiresAction?: boolean;
}>;

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const { query } = router;
    const { fetchCart } = useCurrentCart(false);
    const domainConfig = useDomainConfig();
    const { url } = domainConfig;
    const [isMaxTransactionCountReached, setIsMaxTransactionCountReached] = useState(false);
    const hasRetriedMissingOrderInSafariRef = useRef(false);
    const { orderUuid, companyNumber, orderEmail, orderPaymentType, orderUrlHash, requiresAction } =
        query as OrderConfirmationUrlQuery;
    const isGoPayOrder = orderPaymentType === TypePaymentTypeEnum.GoPay;

    // Synchronous GoPay session detection — before first render, no flash.
    // First visit (fresh checkout): session doesn't exist yet → no redirect.
    // Return visit (back from retry iframe): session exists → redirect to payment-confirmation.
    const [goPayRedirectUrl] = useState<string | null>(() => {
        if (typeof window === 'undefined') {
            return null;
        }

        if (!orderUuid) {
            return null;
        }

        const redirectUrl = buildPaymentConfirmationUrlFromSession(domainConfig, orderUuid);

        if (!redirectUrl) {
            return null;
        }

        return redirectUrl;
    });

    useEffect(() => {
        if (goPayRedirectUrl) {
            router.replace(goPayRedirectUrl);
        }
    }, [goPayRedirectUrl, router]);

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.order_confirmation);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: orderSentPageContentData, fetching: isOrderSentPageContentFetching }] = useOrderSentPageContentQuery(
        {
            variables: { orderUuid: orderUuid! },
        },
    );

    const orderDetailQueryVariables = {
        urlHash: orderUrlHash || undefined,
        uuid: orderUrlHash ? undefined : orderUuid,
    };
    const [{ data: orderData, fetching: isOrderFetching }, reexecuteOrderDetailQuery] = useOrderDetailByHashOrUuidQuery(
        {
            variables: orderDetailQueryVariables,
            pause: !orderUrlHash && !orderUuid,
        },
    );

    const order = orderData?.order;

    const orderDetailUrlFromQuery = orderUrlHash
        ? getInternationalizedStaticUrls([{ url: '/order-detail/:urlHash', param: orderUrlHash }], url)[0]
        : undefined;

    const orderDetailUrl = order?.urlHash
        ? getInternationalizedStaticUrls([{ url: '/order-detail/:urlHash', param: order.urlHash }], url)[0]
        : orderDetailUrlFromQuery;
    const maxTransactionOrderDetailUrl = orderDetailUrl ?? orderDetailUrlFromQuery;

    const isFetchingData = isOrderSentPageContentFetching || isOrderFetching;
    const isOrderMissing = !order && !isFetchingData;

    const pageTitle = t('Thank you for your order');
    const pageHeading = isOrderMissing ? t('Order confirmation') : t('Your order was created');

    const onFetchCart = useEffectEvent(() => {
        fetchCart();
    });

    useEffect(() => {
        onFetchCart();
    }, []);

    useEffect(() => {
        if (
            typeof window === 'undefined' ||
            hasRetriedMissingOrderInSafariRef.current ||
            isOrderFetching ||
            order ||
            !orderUuid ||
            !isGoPayOrder
        ) {
            return;
        }

        const userAgent = window.navigator.userAgent;
        const isSafariBrowser =
            /Safari/i.test(userAgent) && !/Chrome|Chromium|CriOS|Android|FxiOS|Firefox|EdgiOS|Edg\//i.test(userAgent);

        if (!isSafariBrowser) {
            return;
        }

        // Safari occasionally lands on order confirmation before the anonymous order detail becomes readable.
        // Keep this workaround isolated so we can remove it easily if it does not help.
        hasRetriedMissingOrderInSafariRef.current = true;
        reexecuteOrderDetailQuery({ requestPolicy: 'network-only' });
    }, [isGoPayOrder, isOrderFetching, order, orderUuid, reexecuteOrderDetailQuery]);

    const orderPayment = order?.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment);
    const orderTransport = order?.items.find((item) => item.type === TypeOrderItemTypeEnum.Transport);
    const orderRounding = order?.items.find((item) => item.type === TypeOrderItemTypeEnum.Rounding);

    // Guarded fallback: emit ec.payment only when GoPay redirect is NOT expected
    const { tryEmitPaymentEvent } = useEmitPendingPaymentEvent();

    useEffect(() => {
        if (!order || !orderUuid) {
            return;
        }

        // Stale browser-back landings from GoPay can return to the original
        // order-confirmation history entry with requiresAction still present.
        // In that case the payment attempt is still recoverable on
        // /order-payment-confirmation and must not be closed as a final fail here.
        if (isGoPayOrder && requiresAction) {
            return;
        }

        tryEmitPaymentEvent({
            orderUuid,
            isPaid: order.isPaid,
            hasPaymentInProcess: order.hasPaymentInProcess,
            paymentTransactionsCount: order.paymentTransactionsCount,
            paymentName: orderPayment?.name ?? '',
            orderNumber: order.number,
        });
    }, [isGoPayOrder, order, orderUuid, orderPayment?.name, requiresAction]); // eslint-disable-line react-hooks/exhaustive-deps

    const stepperFlow =
        order?.hasExternalPayment && order.isPaid ? FlowTypesEnum.PaymentSuccess : FlowTypesEnum.PaymentAwaiting;

    const maxTransactionCountReachedContent = maxTransactionOrderDetailUrl ? (
        <Trans
            i18nKey="Max transaction count reached. Please go to the <link>order detail page</link> and pay with another method."
            components={{
                link: (
                    <ExtendedNextLink
                        aria-label={t('Go to order detail page', { ns: 'accessibility' })}
                        href={maxTransactionOrderDetailUrl}
                        type="orderDetail"
                    />
                ),
            }}
        />
    ) : (
        t('Max transaction count reached. Please go to the order detail page and pay with another method.')
    );

    if (goPayRedirectUrl) {
        return <CommonLayout isFetchingData pageTypeOverride="order-confirmation" title={pageTitle} />;
    }

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={pageTitle}>
                <Webline tid={TIDs.pages_orderconfirmation}>
                    {isMaxTransactionCountReached ? (
                        <ConfirmationPageContent
                            heading={t('Payment could not be started')}
                            headingClassName="text-text-error"
                            orderDetailUrl={orderDetailUrl}
                            warningMessage={maxTransactionCountReachedContent}
                        />
                    ) : (
                        <ConfirmationPageContent
                            content={orderSentPageContentData?.orderSentPageContent}
                            heading={pageHeading}
                            orderDetailUrl={orderDetailUrl}
                            warningMessage={
                                isOrderMissing ? (
                                    orderDetailUrlFromQuery ? (
                                        <Trans
                                            i18nKey="We couldn't display the order confirmation details. <link>View order details</link>"
                                            components={{
                                                link: (
                                                    <ExtendedNextLink
                                                        href={orderDetailUrlFromQuery}
                                                        type="orderDetail"
                                                        aria-label={t('Go to order detail page', {
                                                            ns: 'accessibility',
                                                        })}
                                                    />
                                                ),
                                            }}
                                        />
                                    ) : (
                                        t(
                                            "We couldn't display the order confirmation details. Check your email for order details.",
                                        )
                                    )
                                ) : undefined
                            }
                        >
                            {!!order && isGoPayOrder && (
                                <GoPayGateway
                                    className="mt-4"
                                    initialButtonText={t('Repeat payment')}
                                    orderNumber={order.number}
                                    orderUrlHash={orderUrlHash || order.urlHash}
                                    orderUuid={order.uuid}
                                    paymentName={orderPayment?.name}
                                    paymentTransactionsCount={order.paymentTransactionsCount}
                                    requiresAction={requiresAction}
                                    onMaxTransactionCountReached={() => setIsMaxTransactionCountReached(true)}
                                />
                            )}
                        </ConfirmationPageContent>
                    )}

                    {!!order && (
                        <>
                            <OrderConfirmationStepper flow={stepperFlow} />

                            <div className="grid vl:grid-cols-3 gap-4 vl:gap-10">
                                <div className="vl:col-span-2 flex vl:flex-col flex-col-reverse gap-4">
                                    <OrderCustomerInfo order={order} />

                                    <RegistrationAfterOrder
                                        companyNumber={companyNumber}
                                        orderEmail={orderEmail || order.email}
                                        orderUrlHash={orderUrlHash}
                                        orderUuid={orderUuid}
                                    />
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
                        </>
                    )}
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const { orderUuid, orderEmail } = context.query as OrderConfirmationUrlQuery;

    if (!orderUuid || !orderEmail) {
        return {
            redirect: {
                destination: getBasePathWithLocale(
                    getInternationalizedStaticUrls(['/cart'], domainConfig.url)[0],
                    context,
                ),
                statusCode: 301,
            },
        };
    }

    return initServerSideProps<TypeOrderSentPageContentQueryVariables>({
        context,
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
        prefetchedQueries: [
            {
                query: OrderSentPageContentQueryDocument,
                variables: { orderUuid },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderConfirmationPage;
