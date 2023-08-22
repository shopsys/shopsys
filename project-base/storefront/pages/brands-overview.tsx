import { CommonLayout } from 'components/Layout/CommonLayout';
import { BrandsContent } from 'components/Pages/Brands/BrandsContent';
import { BrandsQueryDocumentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { GtmPageType } from 'gtm/types/enums';

const BrandsOverviewPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Brands')}>
            <BrandsContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    return initServerSideProps({
        context,
        prefetchedQueries: [{ query: BrandsQueryDocumentApi }],
        redisClient,
        domainConfig,
        t,
    });
});

export default BrandsOverviewPage;
