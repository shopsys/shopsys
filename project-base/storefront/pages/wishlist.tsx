import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { GtmPageType } from 'types/gtm/enums';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useRouter } from 'next/router';
import SharedWishlist from 'components/Pages/Wishlist/SharedWishlist';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';

const WishlistPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    useGtmPageViewEvent(useGtmStaticPageViewEvent(GtmPageType.other));
    const currentDomainConfig = useDomainConfig();

    const [wishlistUrl] = getInternationalizedStaticUrls(['/wishlist'], currentDomainConfig.url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Wishlist'), slug: wishlistUrl }];
    const router = useRouter();
    const urlQueryParamId = router.query.id as string | undefined;

    return (
        <CommonLayout title={t('Wishlist')}>
            <Webline>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            {urlQueryParamId ? <SharedWishlist urlQueryParamId={urlQueryParamId} /> : <Wishlist />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) => initServerSideProps({ context, redisClient }),
);

export default WishlistPage;
