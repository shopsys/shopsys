import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { PageGuard } from 'components/Helpers/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderConfirmationContent } from 'components/Pages/OrderConfirmation/OrderConfirmationContent';
import { Registration } from 'components/Pages/OrderConfirmation/Registration/Registration';
import { OrderSentPageContentDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { canAccessOrderConfirmation } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainUrl);
    const { isUserLoggedIn } = useCurrentUserData();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('purchase');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <PageGuard accessCondition={canAccessOrderConfirmation} errorRedirectUrl={cartUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Thank you for your order')}>
                <OrderConfirmationContent />
                {!isUserLoggedIn && <Registration />}
            </CommonLayout>
        </PageGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, false, [
        { query: OrderSentPageContentDocumentApi, variables: { orderUuid: store.getState().user.lastOrderUuid } },
    ]);
});

export default OrderConfirmationPage;
