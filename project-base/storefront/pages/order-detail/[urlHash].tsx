import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetailContent';
import {
    BreadcrumbFragmentApi,
    OrderDetailByHashQueryDocumentApi,
    useOrderDetailByHashQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { GtmPageType } from 'gtm/types/enums';

const OrderDetailByHashPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const [{ data: orderData }] = useOrderDetailByHashQueryApi({
        variables: { urlHash: getStringFromUrlQuery(router.query.urlHash) },
    });
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard accessCondition={!!orderData?.order} errorRedirectUrl="/">
                {!!orderData?.order && (
                    <CommonLayout title={`${t('Order number')} ${orderData.order.number}`} breadcrumbs={breadcrumbs}>
                        <OrderDetailContent order={orderData.order} />
                    </CommonLayout>
                )}
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (typeof context.params?.urlHash !== 'string') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        prefetchedQueries: [
            { query: OrderDetailByHashQueryDocumentApi, variables: { urlHash: context.params.urlHash } },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderDetailByHashPage;
