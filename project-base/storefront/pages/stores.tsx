import { StoresWrapper } from 'components/Blocks/StoreList/StoresWrapper';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { useStoresQuery, StoresQueryDocument } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const [{ data: storesData, fetching: isStoresFetching }] = useStoresQuery();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} isFetchingData={isStoresFetching} title={t('Stores')}>
            <SimpleLayout standardWidth heading={t('Stores')}>
                {storesData?.stores && <StoresWrapper />}
            </SimpleLayout>
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                prefetchedQueries: [{ query: StoresQueryDocument }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;
