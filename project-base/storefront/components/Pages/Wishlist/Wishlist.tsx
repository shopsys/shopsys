import { InfoIcon, RemoveThinIcon } from 'components/Basic/Icon/IconsSvg';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useWishlist } from 'hooks/useWishlist';
import useTranslation from 'next-translate/useTranslation';

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, fetching, handleCleanWishlist } = useWishlist();
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
                                handleCleanWishlist();
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

export default Wishlist;
