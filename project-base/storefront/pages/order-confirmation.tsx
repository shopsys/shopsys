import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import {
    OrderSentPageContentDocumentApi,
    useIsCustomerUserRegisteredQueryApi,
    useOrderSentPageContentApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { PaymentTypeEnum } from 'types/payment';

export type OrderConfirmationQuery = {
    orderUuid: string | undefined;
    orderEmail: string | undefined;
    orderPaymentType: string | undefined;
    registrationData?: string;
};

const TEST_IDENTIFIER = 'pages-orderconfirmation';

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { query } = useRouter();
    const { orderUuid, orderEmail, orderPaymentType, registrationData } = query as OrderConfirmationQuery;
    const isUserLoggedIn = useIsUserLoggedIn();
    const parsedRegistrationData = useRef<ContactInformation | undefined>(
        registrationData ? (JSON.parse(registrationData) as ContactInformation) : undefined,
    );
    const { fetchCart, isWithCart } = useCurrentCart(false);

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.order_confirmation);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: orderSentPageContentData, fetching: isOrderPageContentFetching }] = useOrderSentPageContentApi({
        variables: { orderUuid: orderUuid! },
    });
    const [{ data: isCustomerUserRegisteredData, fetching: isInformationAboutUserRegistrationFetching }] =
        useIsCustomerUserRegisteredQueryApi({
            variables: {
                email: orderEmail!,
            },
            pause: !orderEmail,
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
                <Webline>
                    {orderSentPageContentData && !isOrderPageContentFetching && (
                        <div
                            className="my-16 flex flex-col items-center justify-center gap-9 lg:mb-20 lg:flex-row"
                            data-testid={TEST_IDENTIFIER}
                        >
                            <img
                                alt="Objednávka odeslána"
                                className="w-40"
                                src="/public/frontend/images/sent-cart.svg"
                            />

                            <div className="flex flex-col gap-8 text-center lg:text-left">
                                <div
                                    dangerouslySetInnerHTML={{
                                        __html: orderSentPageContentData.orderSentPageContent,
                                    }}
                                />

                                {orderPaymentType === PaymentTypeEnum.GoPay && <GoPayGateway orderUuid={orderUuid!} />}
                            </div>
                        </div>
                    )}

                    {isOrderPageContentFetching && <CartLoading />}

                    {!!parsedRegistrationData.current &&
                        !isUserLoggedIn &&
                        orderUuid &&
                        !isInformationAboutUserRegistrationFetching &&
                        isCustomerUserRegisteredData?.isCustomerUserRegistered === false && (
                            <RegistrationAfterOrder
                                lastOrderUuid={orderUuid}
                                registrationData={parsedRegistrationData.current}
                            />
                        )}
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
                destination: getInternationalizedStaticUrls(['/cart'], domainConfig.url)[0] ?? '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        prefetchedQueries: [
            {
                query: OrderSentPageContentDocumentApi,
                variables: { orderUuid },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderConfirmationPage;
