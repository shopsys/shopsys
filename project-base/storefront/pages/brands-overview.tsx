import { CommonLayout } from 'components/Layout/CommonLayout';
import { BrandsContent } from 'components/Pages/Brands/BrandsContent';
import { BrandsQueryDocument } from 'graphql/requests/brands/queries/BrandsQuery.generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';

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
        prefetchedQueries: [{ query: BrandsQueryDocument }],
        redisClient,
        domainConfig,
        t,
    });
});

export default BrandsOverviewPage;
