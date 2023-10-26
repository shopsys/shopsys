import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useSharedWishlist } from 'hooks/useWishlist';
import useTranslation from 'next-translate/useTranslation';

type SharedWishlistProps = {
    urlQueryParamId: string;
};

export const SharedWishlist: FC<SharedWishlistProps> = ({ urlQueryParamId }) => {
    const { t } = useTranslation();
    const { products, fetching } = useSharedWishlist(urlQueryParamId.split(','));

    if (fetching) {
        return <SkeletonModuleWishlist />;
    }

    if (!products.length) {
        return (
            <Webline className="my-14">
                <div className="h3">{t('There are no products in the shared wishlist.')}</div>
            </Webline>
        );
    }

    return (
        <Webline>
            <h1 className="mb-7">{t('Shared wishlist')}</h1>

            <ProductsList
                fetching={fetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.sharedWishlist}
                products={products}
            />
        </Webline>
    );
};

export default SharedWishlist;
