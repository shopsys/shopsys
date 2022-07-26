import MetaRobots from 'components/Basic/Head/MetaRobots';
import PageGuard from 'components/Helpers/PageGuard';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import OrderDetail from 'components/Pages/Customer/OrderDetail';
import { useOrderDetailByHash } from 'connectors/customer/Orders';
import { OrderDetailByHashQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const OrderDetailByHash: FC = () => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const router = useRouter();
    const order = useOrderDetailByHash(getStringFromUrlQuery(router.query.urlHash), domainConfig);
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], domainConfig.url);
    const breadcrumbs = useMemo(() => [{ name: t('My orders'), slug: customerOrdersUrl }], [customerOrdersUrl, t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainConfig.url}>
            <MetaRobots content="noindex" />
            <PageGuard accessCondition={order !== null} errorRedirectUrl="/">
                <CommonLayout title={`${t('Order number')} ${order?.number}`}>
                    <OrderDetail order={order!} breadcrumbs={breadcrumbs} />
                </CommonLayout>
            </PageGuard>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    if (typeof context.params?.urlHash !== 'string') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    initDomainConfig(context, store);
    return initServerSideProps(context, store, false, [
        { query: OrderDetailByHashQueryDocumentApi, variables: { urlHash: context.params.urlHash } },
    ]);
});

export default OrderDetailByHash;
