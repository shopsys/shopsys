import { CommonLayout } from 'components/Layout/CommonLayout';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { NextPage } from 'next';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const WishlistPage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const currentDomainConfig = useDomainConfig();
    const [wishlistUrl] = getInternationalizedStaticUrls(['/wishlist'], currentDomainConfig.url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Wishlist'), slug: wishlistUrl }];

    useGtmPageReadyEvent(useGtmStaticPageReadyEvent(GtmPageType.wishlist, breadcrumbs));

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Wishlist')}>
            <Wishlist />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default WishlistPage;
