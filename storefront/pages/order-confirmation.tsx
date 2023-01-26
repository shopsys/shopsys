import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { PageGuard } from 'components/Helpers/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderConfirmationContent } from 'components/Pages/OrderConfirmation/OrderConfirmationContent';
import { Registration } from 'components/Pages/OrderConfirmation/Registration/Registration';
import { OrderSentPageContentDocumentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            const orderUuid = store.getState().user.lastOrderUuid;

            if (typeof orderUuid !== 'string' || orderUuid.length === 0) {
                return {
                    redirect: {
                        destination: getInternationalizedStaticUrls(['/cart'], store.getState().domain.url)[0] ?? '/',
                        statusCode: 301,
                    },
                };
            }

            return initServerSideProps({
                context,
                store,
                prefetchedQueries: [
                    {
                        query: OrderSentPageContentDocumentApi,
                        variables: { orderUuid },
                    },
                ],
                redisClient,
            });
        },
        store,
    ),
);

export default OrderConfirmationPage;
