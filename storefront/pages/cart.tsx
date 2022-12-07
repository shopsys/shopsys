import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartList } from 'components/Pages/Cart/CartList/CartList';
import { CartSummary } from 'components/Pages/Cart/CartSummary/CartSummary';
import { EmptyCartWrapper } from 'components/Pages/Cart/EmptyCartWrapper';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmCartView } from 'hooks/gtm/useGtmCartView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const CartPage: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const t = useTypedTranslationFunction();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('cart');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmCartView(gtmStaticPageViewEvent);
    const currentCart = useCurrentCart();

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <EmptyCartWrapper currentCart={currentCart} title={t('Cart')} isCartPage>
                <CommonLayout title={t('Cart')}>
                    <OrderSteps activeStep={1} domainUrl={domainUrl} />
                    <CartList items={currentCart.cart?.items} />
                    <CartSummary />
                    <Webline>
                        <OrderAction
                            buttonBack={t('Back')}
                            buttonNext={t('Transport and payment')}
                            hasDisabledLook={false}
                            withGapTop={false}
                            withGapBottom
                            buttonBackLink="/"
                            buttonNextLink={transportAndPaymentUrl}
                        />
                    </Webline>
                </CommonLayout>
            </EmptyCartWrapper>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default CartPage;
