import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from 'components/Blocks/Product/ProductExposedStoresCount';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'helpers/gtm/eventHandlers';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

type ProductItemProps = {
    product: ListedProductFragmentApi;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
};

const getDataTestId = (catalogNumber: string) => 'blocks-product-list-listeditem-' + catalogNumber;

export const ProductItem: FC<ProductItemProps> = ({
    product,
    listIndex,
    gtmProductListName,
    gtmMessageOrigin,
    isProductInComparison,
    toggleProductInComparison,
    isProductInWishlist,
    toggleProductInWishlist,
}) => {
    const { url } = useDomainConfig();
    const t = useTypedTranslationFunction();

    return (
        <div
            className="relative flex flex-col justify-between rounded-t-xl border-greyLighter p-3 text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl vl:border-b"
            data-testid={getDataTestId(product.catalogNumber)}
        >
            {gtmProductListName === GtmProductListNameType.wishlist && (
                <button
                    className="absolute right-3 z-above flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke p-0 outline-none transition hover:bg-blueLight"
                    onClick={toggleProductInWishlist}
                    data-testid={getDataTestId(product.catalogNumber) + '-wishlist-remove'}
                    title={t('Remove from wishlist')}
                >
                    <Icon iconType="icon" icon="RemoveBold" className="mx-auto w-2 basis-2" />
                </button>
            )}
            <ExtendedNextLink
                type="product"
                href={product.slug}
                className="relative flex h-full flex-col no-underline hover:no-underline"
                onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
            >
                <>
                    <div className="relative flex h-[185px] w-full items-center justify-center px-3 pt-4 pb-3">
                        <Image
                            image={product.mainImage}
                            type="list"
                            alt={product.mainImage?.name || product.fullName}
                            className="max-h-full lg:hover:mix-blend-multiply"
                        />
                        <div className="absolute top-3 left-4 flex flex-col">
                            <ProductFlags flags={product.flags} />
                        </div>
                    </div>

                    <div className="mt-auto flex-1 px-3 pb-5">
                        <h3 className="mb-1 block h-10 overflow-hidden break-words text-lg font-bold leading-5 text-black">
                            {product.fullName}
                        </h3>

                        <ProductPrice productPrice={product.price} />

                        <div className="text-sm text-black">
                            {product.availability.name}
                            <ProductAvailableStoresCount
                                isMainVariant={product.isMainVariant}
                                availableStoresCount={product.availableStoresCount}
                            />
                            <ProductExposedStoresCount
                                isMainVariant={product.isMainVariant}
                                exposedStoresCount={product.exposedStoresCount}
                            />
                        </div>
                    </div>
                </>
            </ExtendedNextLink>
            <div className="mb-2 flex justify-end gap-2">
                <ProductCompareButton
                    isProductInComparison={isProductInComparison}
                    toggleProductInComparison={toggleProductInComparison}
                />
                <ProductWishlistButton
                    toggleProductInWishlist={toggleProductInWishlist}
                    isProductInWishlist={isProductInWishlist}
                />
            </div>
            <ProductAction
                product={product}
                gtmProductListName={gtmProductListName}
                gtmMessageOrigin={gtmMessageOrigin}
                listIndex={listIndex}
            />
        </div>
    );
};
