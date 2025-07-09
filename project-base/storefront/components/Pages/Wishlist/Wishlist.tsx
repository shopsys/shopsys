import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { Button } from 'components/Forms/Button/Button';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, isProductListFetching, removeWishlist: handleRemoveWishlist } = useWishlist();
    const title = `${t('Wishlist')}${wishlist?.products.length ? ` (${wishlist.products.length})` : ''}`;

    return (
        <VerticalStack gap="md">
            <Webline>
                <h1 className="mb-4">{title}</h1>

                {isProductListFetching && <SkeletonModuleWishlist />}

                {wishlist?.products && !isProductListFetching && (
                    <>
                        <div className="flex w-full flex-col items-center justify-between pb-2 lg:flex-row">
                            <Button
                                aria-label={t('Remove all product from wishlist')}
                                variant="inverted"
                                onClick={() => {
                                    handleRemoveWishlist();
                                }}
                            >
                                {t('Remove all from wishlist')}
                                <RemoveIcon className="size-3" />
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
                    <div className="my-28 flex items-center justify-center">
                        <InfoIcon className="mr-4 w-8" />
                        <div className="h3">{t('There are no products in the wishlist. Add some first.')}</div>
                    </div>
                )}
            </Webline>

            <LastVisitedProducts />
        </VerticalStack>
    );
};
