import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Footer } from 'components/Layout/Footer/Footer';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { EmptyCartWrapper } from 'components/Pages/Cart/EmptyCartWrapper';
import { TransportAndPaymentContent } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentContent';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useLastOrderQueryApi, useTransportsQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useGtmPaymentAndTransportPageViewEvent } from 'hooks/gtm/useGtmPaymentAndTransportPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { GtmPageType } from 'types/gtm/enums';

const TransportAndPaymentPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { isUserLoggedIn } = useCurrentUserData();
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
            <MetaRobots content="noindex" />
            <EmptyCartWrapper currentCart={currentCart} title={t('Order')}>
                <OrderLayout
                    activeStep={2}
                    isTransportOrPaymentLoading={Boolean(isTransportSelectionLoading) || isPaymentSelectionLoading}
                >
                    {data === undefined && isUserLoggedIn ? (
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
