import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Webline } from 'components/Layout/Webline/Webline';
import { Heading } from 'components/Basic/Heading/Heading';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { useSharedWishlist } from 'hooks/useWishlist';

type SharedWishlistProps = {
    urlQueryParamId: string;
};

export const SharedWishlist: FC<SharedWishlistProps> = ({ urlQueryParamId }) => {
    const t = useTypedTranslationFunction();
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
                                <Heading type="h1" className="!mb-0 !leading-7">
                                    {t('Shared wishlist')}
                                </Heading>
                            </div>
                        </div>
                    </div>

                    <ProductsList
                        products={products}
                        gtmProductListName={GtmProductListNameType.sharedWishlist}
                        fetching={fetching}
                        gtmMessageOrigin={GtmMessageOriginType.other}
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
