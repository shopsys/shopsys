import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { Footer } from 'components/Layout/Footer/Footer';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTransports } from 'connectors/transports/Transports';
import { useLastOrderQueryApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { handleOrderPagesRedirect } from 'helpers/misc/handleOrderPagesRedirect';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { createClient } from 'helpers/urql/createClient';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';
import { useGtmPaymentShippingView } from 'hooks/gtm/useGtmPaymentShippingView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import React, { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { ssrExchange } from 'urql';

const TransportAndPaymentPage: FC<ServerSidePropsType> = () => {
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { isUserLoggedIn } = useCurrentUserData();
    const transports = useTransports(cartUuid);
    const [{ data }] = useLastOrderQueryApi({ requestPolicy: 'network-only', pause: !isUserLoggedIn });
    const currentCart = useCurrentCart();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [changeTransportInCart, isTransportSelectionLoading] = useChangeTransportInCart();
    const [changePaymentInCart, isPaymentSelectionLoading] = useChangePaymentInCart();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('transport pay');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmPaymentShippingView(gtmStaticPageViewEvent);

    if (transports.length === 0 || (data === undefined && isUserLoggedIn)) {
        return null;
    }

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <OrderLayout
                activeStep={2}
                isTransportOrPaymentLoading={isTransportSelectionLoading || isPaymentSelectionLoading}
            >
                {currentCart.isInitiallyLoaded && (
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
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient((redisClient) => async (context) => {
        initDomainConfig(context, store);
        const ssrCache = ssrExchange({ isClient: false });
        const client = await createClient(context, store, ssrCache, redisClient);
        const redirect = await handleOrderPagesRedirect(context, store, client);

        return redirect === false ? initServerSideProps({ context, store, client, ssrCache, redisClient }) : redirect;
    }),
);

export default TransportAndPaymentPage;
