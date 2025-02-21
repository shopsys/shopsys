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
import { TIDs } from 'cypress/tids';
import { useOrderDetailByHashOrUuidQuery } from 'graphql/requests/orders/queries/OrderDetailByHashOrUuidQuery.generated';
import {
    useOrderSentPageContentQuery,
    TypeOrderSentPageContentQueryVariables,
    OrderSentPageContentQueryDocument,
} from 'graphql/requests/orders/queries/OrderSentPageContentQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { PaymentTypeEnum } from 'types/payment';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type OrderConfirmationUrlQuery = {
    orderUuid: string | undefined;
    companyNumber: string | undefined;
    orderEmail: string | undefined;
    orderPaymentType: string | undefined;
    orderUrlHash?: string | undefined;
    orderPaymentStatusPageValidityHash: string | undefined;
};

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { query } = useRouter();
    const { fetchCart } = useCurrentCart(false);
    const { orderUuid, orderPaymentType, companyNumber, orderEmail, orderUrlHash } = query as OrderConfirmationUrlQuery;

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.order_confirmation);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: orderSentPageContentData, fetching: isOrderSentPageContentFetching }] = useOrderSentPageContentQuery(
        {
            variables: { orderUuid: orderUuid! },
        },
    );

    const [{ data: orderData }] = useOrderDetailByHashOrUuidQuery({
        variables: {
            urlHash: orderUrlHash,
            uuid: orderUuid,
        },
    });

    useEffect(() => {
        fetchCart();
    }, []);

    if (!orderData?.order) {
        return null;
    }

    const stepperFlow =
        orderData.order.payment.type === PaymentTypeEnum.GoPay && orderData.order.isPaid
            ? FlowTypesEnum.PaymentSuccess
            : FlowTypesEnum.PaymentAwaiting;

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
                        heading={t('Your order was created')}
                    >
                        {orderPaymentType === PaymentTypeEnum.GoPay ? (
                            <GoPayGateway orderUuid={orderUuid!} />
                        ) : undefined}
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
                                totalPrice={orderData.order.totalPrice}
                                payment={{
                                    name: orderData.order.payment.name,
                                    price: orderData.order.payment.price.priceWithVat,
                                }}
                                transport={{
                                    name: orderData.order.transport.name,
                                    price: orderData.order.transport.price.priceWithVat,
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
                destination: getInternationalizedStaticUrls(['/cart'], domainConfig.url)[0],
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
