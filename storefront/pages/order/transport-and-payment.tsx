import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Footer } from 'components/Layout/Footer/Footer';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { EmptyCartWrapper } from 'components/Pages/Cart/EmptyCartWrapper';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTransports } from 'connectors/transports/Transports';
import { useLastOrderQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';
import { useGtmPaymentShippingView } from 'hooks/gtm/useGtmPaymentShippingView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import React, { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const TransportAndPaymentPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { isUserLoggedIn } = useCurrentUserData();
    const transports = useTransports(cartUuid);
    const [{ data }] = useLastOrderQueryApi({ requestPolicy: 'network-only', pause: !isUserLoggedIn });
    const currentCart = useCurrentCart();
    const [changeTransportInCart, isTransportSelectionLoading] = useChangeTransportInCart();
    const [changePaymentInCart, isPaymentSelectionLoading] = useChangePaymentInCart();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('transport pay');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmPaymentShippingView(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <EmptyCartWrapper currentCart={currentCart} title={t('Order')}>
                <OrderLayout
                    activeStep={2}
                    isTransportOrPaymentLoading={Boolean(isTransportSelectionLoading) || isPaymentSelectionLoading}
                >
                    {transports.length === 0 || (data === undefined && isUserLoggedIn) ? (
                        <LoaderWithOverlay />
                    ) : (
                        <TransportAndPaymentContent
                            transports={transports}
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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default TransportAndPaymentPage;
