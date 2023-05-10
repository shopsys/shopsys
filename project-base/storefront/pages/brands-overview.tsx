import { CommonLayout } from 'components/Layout/CommonLayout';
import { BrandsContent } from 'components/Pages/Brands/BrandsContent';
import { BrandsQueryDocumentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'types/gtm/enums';

const BrandsOverviewPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Brands')}>
            <BrandsContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    return initServerSideProps({
        context,
        prefetchedQueries: [{ query: BrandsQueryDocumentApi }],
        redisClient,
    });
});

export default BrandsOverviewPage;
