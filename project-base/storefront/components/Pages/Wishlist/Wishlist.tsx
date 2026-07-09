import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

const RemoveAllProductsPopup = dynamic(
    () =>
        import('components/Blocks/Popup/RemoveAllProductsPopup').then((component) => component.RemoveAllProductsPopup),
    {
        ssr: false,
    },
);

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, isProductListFetching, removeWishlist } = useWishlist();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const title = `${t('Wishlist')}${wishlist?.products.length ? ` (${wishlist.products.length})` : ''}`;

    const handleRemoveAllClick = () => {
        updatePortalContent(
            <RemoveAllProductsPopup
                removeAllHandler={removeWishlist}
                title={t('Do you really want to remove all products from wishlist?')}
            />,
        );
    };

    return (
        <VerticalStack gap="md">
            <Webline>
                {isProductListFetching && <SkeletonModuleWishlist />}

                {wishlist?.products && !isProductListFetching && (
                    <>
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-4">
                            <h1>{title}</h1>

                            <Button
                                aria-label={t('Remove all product from wishlist', { ns: 'accessibility' })}
                                variant="inverted"
                                onClick={handleRemoveAllClick}
                            >
                                <TrashCanIcon className="size-4" />
                                {t('Remove all from wishlist')}
                            </Button>
                        </div>

                        <ProductsList
                            areProductsFetching={isProductListFetching}
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.wishlist}
                            products={wishlist.products}
                        />
                    </>
                )}

                {!wishlist?.products && !isProductListFetching && (
                    <PageHero
                        actionHref="/"
                        actionSkeletonType="homepage"
                        actionTitle={t('Discover our products')}
                        icon={HeartIcon}
                        title={t('Wishlist')}
                        description={t(
                            'Your wishlist is feeling empty! Add products you love so you can easily find them here later.',
                        )}
                    />
                )}
            </Webline>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
