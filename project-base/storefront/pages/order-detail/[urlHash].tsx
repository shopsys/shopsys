import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Helpers/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetailContent';
import {
    BreadcrumbFragmentApi,
    OrderDetailByHashQueryDocumentApi,
    useOrderDetailByHashQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { GtmPageType } from 'types/gtm/enums';

const OrderDetailByHashPage: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const router = useRouter();
    const [{ data: orderData }] = useQueryError(
        useOrderDetailByHashQueryApi({ variables: { urlHash: getStringFromUrlQuery(router.query.urlHash) } }),
    );
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard
                accessCondition={orderData?.order !== undefined && orderData.order !== null}
                errorRedirectUrl="/"
            >
                {orderData?.order !== undefined && orderData.order !== null && (
                    <CommonLayout title={`${t('Order number')} ${orderData.order.number}`}>
                        <OrderDetailContent order={orderData.order} breadcrumbs={breadcrumbs} />
                    </CommonLayout>
                )}
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
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
    });
});

export default OrderDetailByHashPage;
