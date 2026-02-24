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
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import Trans from 'next-translate/Trans';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent, useState } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
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
    const { query } = useRouter();
    const { fetchCart } = useCurrentCart(false);
    const { url } = useDomainConfig();
    const [isMaxTransactionCountReached, setIsMaxTransactionCountReached] = useState(false);
    const { orderUuid, orderPaymentType, companyNumber, orderEmail, orderUrlHash, requiresAction } =
        query as OrderConfirmationUrlQuery;

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.order_confirmation);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [
        { data: orderSentPageContentData, fetching: isOrderSentPageContentFetching, error: orderSentPageContentError },
    ] = useOrderSentPageContentQuery({
        variables: { orderUuid: orderUuid! },
    });

    const [{ data: orderData }] = useOrderDetailByHashOrUuidQuery({
        variables: {
            urlHash: orderUrlHash,
            uuid: orderUuid,
        },
    });

    const [orderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: orderData?.order?.urlHash }],
        url,
    );

    const onFetchCart = useEffectEvent(() => {
        fetchCart();
    });

    useEffect(() => {
        onFetchCart();
    }, []);

    if (!orderData?.order) {
        return null;
    }

    const stepperFlow =
        orderData.order.hasExternalPayment && orderData.order.isPaid
            ? FlowTypesEnum.PaymentSuccess
            : FlowTypesEnum.PaymentAwaiting;

    const orderPayment = orderData.order.items.find((item) => item.type === TypeOrderItemTypeEnum.Payment);
    const orderTransport = orderData.order.items.find((item) => item.type === TypeOrderItemTypeEnum.Transport);

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout
                isFetchingData={isOrderSentPageContentFetching}
                pageTypeOverride="order-confirmation"
                title={t('Thank you for your order')}
            >
                <Webline tid={TIDs.pages_orderconfirmation}>
                    <ConfirmationPageContent
                        content={orderSentPageContentData?.orderSentPageContent}
                        error={orderSentPageContentError}
                        heading={t('Your order was created')}
                        orderDetailUrl={orderDetailUrl}
                    >
                        {orderPaymentType === TypePaymentTypeEnum.GoPay && (
                            <div className="mt-4">
                                {isMaxTransactionCountReached ? (
                                    <Trans
                                        i18nKey="Max transaction count reached. Please go to the <link>order detail page</link> and pay with another method."
                                        components={{
                                            link: (
                                                <ExtendedNextLink
                                                    aria-label={t('Go to order detail page', { ns: 'accessibility' })}
                                                    href={orderDetailUrl}
                                                    type="orderDetail"
                                                />
                                            ),
                                        }}
                                    />
                                ) : (
                                    <GoPayGateway
                                        initialButtonText={t('Repeat payment')}
                                        orderUuid={orderUuid!}
                                        requiresAction={requiresAction}
                                        onMaxTransactionCountReached={() => setIsMaxTransactionCountReached(true)}
                                    />
                                )}
                            </div>
                        )}
                    </ConfirmationPageContent>

                    <OrderConfirmationStepper flow={stepperFlow} />

                    <div className="vl:grid-cols-3 vl:gap-10 grid gap-4">
                        <div className="vl:col-span-2 vl:flex-col flex flex-col-reverse gap-4">
                            <OrderCustomerInfo order={orderData.order} />

                            <RegistrationAfterOrder
                                companyNumber={companyNumber}
                                orderEmail={orderEmail}
                                orderUrlHash={orderUrlHash}
                                orderUuid={orderUuid}
                            />
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
