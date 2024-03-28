import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { RemoveThinIcon } from 'components/Basic/Icon/RemoveThinIcon';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useWishlist } from 'hooks/productLists/wishlist/useWishlist';
import useTranslation from 'next-translate/useTranslation';

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, fetching, removeWishlist: handleRemoveWishlist } = useWishlist();
    const title = `${t('Wishlist')}${wishlist?.products.length ? ` (${wishlist.products.length})` : ''}`;

    return (
        <>
            <h1 className="mb-3">{title}</h1>

            {fetching && <SkeletonModuleWishlist />}

            {wishlist?.products && !fetching && (
                <>
                    <div className="flex w-full flex-col items-center justify-between border-b border-greyLighter pb-2 lg:flex-row">
                        <div
                            className="mb-2 cursor-pointer items-center rounded bg-greyVeryLight py-2 px-4 transition-colors hover:bg-greyLighter sm:inline-flex lg:mb-0"
                            onClick={() => {
                                handleRemoveWishlist();
                            }}
                        >
                            <span className="mr-3 text-sm">{t('Delete all from wishlist')}</span>
                            <RemoveThinIcon className="w-3" />
                        </div>
                    </div>

                    <div>
                        <ProductsList
                            fetching={fetching}
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.wishlist}
                            products={wishlist.products}
                        />
                    </div>
                </>
            )}

            {!wishlist?.products && !fetching && (
                <div className="flex items-center">
                    <InfoIcon className="mr-4 w-8" />
                    <div className="h3">{t('There are no products in the wishlist. Add some first.')}</div>
                </div>
            )}
        </>
    );
};
