import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { PageGuard } from 'components/Helpers/PageGuard';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetail/OrderDetailContent';
import { useOrderDetailByHash } from 'connectors/customer/Orders';
import { OrderDetailByHashQueryDocumentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const OrderDetailByHashPage: FC = () => {
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
                    <OrderDetailContent order={order!} breadcrumbs={breadcrumbs} />
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

export default OrderDetailByHashPage;
