import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { Button } from 'components/Forms/Button/Button';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, isProductListFetching, removeWishlist: handleRemoveWishlist } = useWishlist();
    const title = `${t('Wishlist')}${wishlist?.products.length ? ` (${wishlist.products.length})` : ''}`;

    return (
        <>
            <h1>{title}</h1>

            {isProductListFetching && <SkeletonModuleWishlist />}

            {wishlist?.products && !isProductListFetching && (
                <>
                    <div className="flex w-full flex-col items-center justify-between pb-2 lg:flex-row">
                        <Button
                            variant="inverted"
                            onClick={() => {
                                handleRemoveWishlist();
                            }}
                        >
                            <span className="mr-3 text-sm">{t('Delete all from wishlist')}</span>
                            <RemoveIcon className="w-3 text-actionInvertedText" />
                        </Button>
                    </div>

                    <div>
                        <ProductsList
                            areProductsFetching={isProductListFetching}
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.wishlist}
                            products={wishlist.products}
                        />
                    </div>
                </>
            )}

            {!wishlist?.products && !isProductListFetching && (
                <div className="flex items-center">
                    <InfoIcon className="mr-4 w-8" />
                    <div className="h3">{t('There are no products in the wishlist. Add some first.')}</div>
                </div>
            )}
        </>
    );
};
