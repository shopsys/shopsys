import MetaRobots from 'components/Basic/Head/MetaRobots';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import Webline from 'components/Layout/Webline';
import CartSummary from 'components/Pages/Cart/CartSummary';
import List from 'components/Pages/Cart/List';
import { useCurrentCart } from 'connectors/cart/Cart';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGtmCartView } from 'hooks/gtm/useGtmCartView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const Cart: FC<ServerSidePropsType> = () => {
    const { cart } = useCurrentCart();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('cart');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmCartView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout>
                <OrderSteps activeStep={1} domainUrl={domainUrl} />
                <List items={cart?.items} />
                <CartSummary />
                <Webline>
                    <OrderAction
                        activeStep={1}
                        buttonBack={t('Back')}
                        buttonNext={t('Transport and payment')}
                        hasDisabledLook={false}
                        withGapTop={false}
                        withGapBottom={true}
                        buttonBackLink="/"
                        buttonNextLink={transportAndPaymentUrl}
                    />
                </Webline>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Cart;
