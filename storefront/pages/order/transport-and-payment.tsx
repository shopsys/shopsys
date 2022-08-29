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
import { handleOrderPagesRedirect } from 'helpers/misc/handleOrderPagesRedirect';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { createClient } from 'helpers/urql/createClient';
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

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('transport pay');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmPaymentShippingView(gtmStaticPageViewEvent);

    if (transports.length === 0 || (data === undefined && isUserLoggedIn)) {
        return null;
    }

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <OrderLayout activeStep={2}>
                {currentCart.isInitiallyLoaded && (
                    <TransportAndPaymentContent transports={transports} lastOrder={data?.lastOrder ?? null} />
                )}
            </OrderLayout>
            <Webline type="dark">
                <Footer simpleFooter />
            </Webline>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const ssrCache = ssrExchange({ isClient: false });
    const client = await createClient(context, store, ssrCache);
    const redirect = await handleOrderPagesRedirect(context, store, client);

    return redirect === false ? initServerSideProps(context, store, false, [], client, ssrCache) : redirect;
});

export default TransportAndPaymentPage;
