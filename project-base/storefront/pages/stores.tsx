import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoresContent } from 'components/Pages/Stores/StoresContent';
import { BreadcrumbFragmentApi, StoresQueryDocumentApi, useStoresQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'types/gtm/enums';

const StoresPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const [{ data: storesData }] = useStoresQueryApi();
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <CommonLayout title={t('Stores')}>
                {storesData?.stores !== undefined && (
                    <StoresContent stores={storesData.stores} breadcrumbs={breadcrumbs} />
                )}
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) =>
        initServerSideProps({
            context,
            prefetchedQueries: [{ query: StoresQueryDocumentApi }],
            redisClient,
        }),
);

export default StoresPage;
