import { CommonLayout } from 'components/Layout/CommonLayout';
import { BrandsContent } from 'components/Pages/Brands/BrandsContent';
import { BrandsQueryDocumentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const BrandsOverviewPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Brands')}>
            <BrandsContent />
        </CommonLayout>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            return initServerSideProps({
                context,
                store,
                prefetchedQueries: [{ query: BrandsQueryDocumentApi }],
                redisClient,
            });
        },
        store,
    ),
);

export default BrandsOverviewPage;
