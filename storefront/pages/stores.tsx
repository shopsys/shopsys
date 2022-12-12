import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoresContent } from 'components/Pages/Stores/StoresContent';
import { useStores } from 'connectors/stores/Stores';
import { StoresQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const StoresPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const stores = useStores();
    const breadcrumbs = useMemo(() => [{ name: t('Department stores'), slug: '' }], [t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('stores', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout title={t('Stores')}>
                <StoresContent stores={stores} breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient((redisClient) => async (context) => {
        initDomainConfig(context, store);

        return initServerSideProps({
            context,
            store,
            prefetchedQueries: [
                {
                    query: StoresQueryDocumentApi,
                },
            ],
            redisClient,
        });
    }),
);

export default StoresPage;
