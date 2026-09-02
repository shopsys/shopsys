import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { StarIcon } from 'components/Basic/Icon/StarIcon';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { MyReviewsContent } from 'components/Pages/Customer/MyReviews/MyReviewsContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    CurrentCustomerUserProductReviewsQueryDocument,
    TypeCurrentCustomerUserProductReviewsQueryVariables,
} from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import {
    SettingsQueryDocument,
    TypeSettingsQuery,
    TypeSettingsQueryVariables,
} from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRef } from 'react';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const MyReviewsPage: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [customerMyReviewsUrl] = getInternationalizedStaticUrls(['/customer/my-reviews'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My reviews'), slug: customerMyReviewsUrl },
    ];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout
                breadcrumbs={breadcrumbs}
                paginationScrollTargetRef={paginationScrollTargetRef}
                title={t('My reviews')}
            >
                <PageHero
                    description={t('Here you can find all your product reviews and their approval status.')}
                    icon={StarIcon}
                    title={t('My reviews')}
                />

                <MyReviewsContent paginationScrollTargetRef={paginationScrollTargetRef} />
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, ssrExchange }) =>
        async (context) => {
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

            const client = createClient({
                t,
                ssrExchange,
                domainConfig,
                redisClient,
                context,
            });

            const settingsResult: OperationResult<TypeSettingsQuery, TypeSettingsQueryVariables> = await client
                .query(SettingsQueryDocument, {})
                .toPromise();
            const areProductReviewsEnabled = settingsResult.data?.settings?.productReviewsEnabled === true;

            return initServerSideProps<TypeCurrentCustomerUserProductReviewsQueryVariables>({
                context,
                client,
                ssrExchange,
                currentCustomerUserPrefetchMode: 'full',
                authenticationConfig: {
                    authenticationRequired: true,
                },
                prefetchedQueries: areProductReviewsEnabled
                    ? [
                          {
                              query: CurrentCustomerUserProductReviewsQueryDocument,
                              variables: {
                                  first: DEFAULT_ORDERS_SIZE,
                                  after: getEndCursor(page, 0, DEFAULT_ORDERS_SIZE),
                              },
                          },
                      ]
                    : [],
                redisClient,
                domainConfig,
            });
        },
);

export default MyReviewsPage;
