import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { BrandsContent } from 'components/Pages/Brands/BrandsContent';
import { BrandsQueryDocument } from 'graphql/requests/brands/queries/BrandsQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const BrandsOverviewPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout title={t('Brands')}>
            <Webline>
                <h1 className="mb-4">{t('Brands')}</h1>
            </Webline>

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
