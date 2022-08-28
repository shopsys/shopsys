import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartContent } from 'components/Pages/Cart/CartContent';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmCartView } from 'hooks/gtm/useGtmCartView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const CartPage: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('cart');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmCartView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Cart')}>
                <OrderSteps activeStep={1} domainUrl={domainUrl} />
                <CartContent />
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
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default CartPage;
