import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import { TIDs } from 'cypress/tids';
import {
    useOrderSentPageContentQuery,
    OrderSentPageContentQueryVariables,
    OrderSentPageContentQueryDocument,
} from 'graphql/requests/orders/queries/OrderSentPageContentQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { PaymentTypeEnum } from 'types/payment';

export type OrderConfirmationQuery = {
    orderUuid: string | undefined;
    orderEmail: string | undefined;
    orderPaymentType: string | undefined;
    registrationData?: string;
};

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { query } = useRouter();
    const { fetchCart, isWithCart } = useCurrentCart(false);
    const { orderUuid, orderPaymentType } = query as OrderConfirmationQuery;

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.order_confirmation);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: orderSentPageContentData, fetching }] = useOrderSentPageContentQuery({
        variables: { orderUuid: orderUuid! },
    });

    useEffect(() => {
        if (isWithCart) {
            fetchCart();
        }
    }, []);

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout title={t('Thank you for your order')}>
                <Webline tid={TIDs.pages_orderconfirmation}>
                    <ConfirmationPageContent
                        content={orderSentPageContentData?.orderSentPageContent}
                        heading={t('Your order was created')}
                        isFetching={fetching}
                        AdditionalContent={
                            orderPaymentType === PaymentTypeEnum.GoPay ? (
                                <GoPayGateway orderUuid={orderUuid!} />
                            ) : undefined
                        }
                    />
                    <RegistrationAfterOrder />
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const { orderUuid, orderEmail } = context.query as OrderConfirmationQuery;

    if (!orderUuid || !orderEmail) {
        return {
            redirect: {
                destination: getInternationalizedStaticUrls(['/cart'], domainConfig.url)[0],
                statusCode: 301,
            },
        };
    }

    return initServerSideProps<OrderSentPageContentQueryVariables>({
        context,
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
