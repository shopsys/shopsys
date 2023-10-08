import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Footer } from 'components/Layout/Footer/Footer';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { EmptyCartWrapper } from 'components/Pages/Cart/EmptyCartWrapper';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useLastOrderQueryApi, useTransportsQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useGtmPaymentAndTransportPageViewEvent } from 'gtm/hooks/useGtmPaymentAndTransportPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { usePersistStore } from 'store/usePersistStore';
import { GtmPageType } from 'gtm/types/enums';
import Script from 'next/script';

const TransportAndPaymentPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const isUserLoggedIn = !!useCurrentCustomerData();
    const [{ data: transportsData }] = useTransportsQueryApi({
        variables: { cartUuid },
        requestPolicy: 'cache-and-network',
    });
    const [{ data }] = useLastOrderQueryApi({ requestPolicy: 'network-only', pause: !isUserLoggedIn });
    const currentCart = useCurrentCart();
    const [changeTransportInCart, isTransportSelectionLoading] = useChangeTransportInCart();
    const [changePaymentInCart, isPaymentSelectionLoading] = useChangePaymentInCart();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.transport_and_payment);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmPaymentAndTransportPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <Script strategy="afterInteractive" src="https://widget.packeta.com/v6/www/js/library.js" />
            <MetaRobots content="noindex" />
            <EmptyCartWrapper currentCart={currentCart} title={t('Order')}>
                <OrderLayout
                    activeStep={2}
                    isTransportOrPaymentLoading={Boolean(isTransportSelectionLoading) || isPaymentSelectionLoading}
                >
                    {!data && isUserLoggedIn ? (
                        <LoaderWithOverlay />
                    ) : (
                        <TransportAndPaymentContent
                            transports={transportsData?.transports}
                            lastOrder={data?.lastOrder ?? null}
                            changeTransportInCart={changeTransportInCart}
                            isTransportSelectionLoading={isTransportSelectionLoading}
                            changePaymentInCart={changePaymentInCart}
                            isPaymentSelectionLoading={isPaymentSelectionLoading}
                        />
                    )}
                </OrderLayout>
                <Webline type="dark">
                    <Footer simpleFooter />
                </Webline>
            </EmptyCartWrapper>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default TransportAndPaymentPage;
