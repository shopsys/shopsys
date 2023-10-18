import { Heading } from 'components/Basic/Heading/Heading';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
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
        return <LoaderWithOverlay />;
    }

    return (
        <Webline>
            {products.length > 0 ? (
                <>
                    <div className="mb-7 flex flex-wrap">
                        <div className="mb-4 flex w-full items-end vl:mb-0 vl:flex-1">
                            <div className="flex-1 vl:flex-none">
                                <Heading className="!mb-0 !leading-7" type="h1">
                                    {t('Shared wishlist')}
                                </Heading>
                            </div>
                        </div>
                    </div>

                    <ProductsList
                        fetching={fetching}
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        gtmProductListName={GtmProductListNameType.sharedWishlist}
                        products={products}
                    />
                </>
            ) : (
                <div className="my-[50px]">
                    <Heading type="h3">{t('There are no products in the shared wishlist.')}</Heading>
                </div>
            )}
        </Webline>
    );
};

export default SharedWishlist;
