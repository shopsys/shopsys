import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAction } from './ProductAction';
import { ProductAvailableStoresCount } from './ProductAvailableStoresCount';
import { ProductExposedStoresCount } from './ProductExposedStoresCount';
import { ProductFlags } from './ProductFlags';
import { ProductPrice } from './ProductPrice';
import { Image } from 'components/Basic/Image/Image';
import { ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'helpers/gtm/eventHandlers';
import { useDomainConfig } from 'hooks/useDomainConfig';

import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';

type SliderProductItemProps = {
    product: ListedProductFragmentApi;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
};

const TEST_IDENTIFIER = 'blocks-product-sliderproductitem-';

export const SliderProductItem: FC<SliderProductItemProps> = ({
    product,
    gtmProductListName,
    listIndex,
    gtmMessageOrigin,
    isProductInComparison,
    toggleProductInComparison,
    isProductInWishlist,
    toggleProductInWishlist,
}) => {
    const { url } = useDomainConfig();

    return (
        <div
            className="keen-slider__slide p-2 text-dark hover:text-primary"
            data-testid={TEST_IDENTIFIER + product.catalogNumber}
        >
            <div className="group relative flex h-full flex-col rounded-xl text-left hover:shadow-lg">
                <ExtendedNextLink
                    href={product.slug}
                    type="product"
                    className="relative flex h-full flex-col no-underline hover:no-underline"
                    onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
                >
                    <>
                        <div
                            className="relative flex h-48 w-full items-center justify-center px-2 pt-4 pb-2"
                            data-testid={TEST_IDENTIFIER + 'image'}
                        >
                            <Image
                                image={product.mainImage}
                                type="list"
                                alt={product.mainImage?.name || product.fullName}
                                className="group-hover:mix-blend-multiply"
                            />
                            <div className="absolute top-2 left-3 flex flex-col">
                                <ProductFlags flags={product.flags} />
                            </div>
                        </div>
                        <div className="mt-auto block flex-1 px-2 pb-5">
                            <h3
                                className="mb-1 block h-10 overflow-hidden break-words text-lg font-bold leading-5 text-black"
                                data-testid={TEST_IDENTIFIER + 'name'}
                            >
                                {product.fullName}
                            </h3>
                            <ProductPrice productPrice={product.price} />
                            <div className="mb-3 text-sm text-black">
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
                        isMainVariant={product.isMainVariant}
                        isProductInComparison={isProductInComparison}
                        toggleProductInComparison={toggleProductInComparison}
                    />
                    <ProductWishlistButton
                        isMainVariant={product.isMainVariant}
                        isProductInWishlist={isProductInWishlist}
                        toggleProductInWishlist={toggleProductInWishlist}
                    />
                </div>
                <ProductAction
                    product={product}
                    gtmProductListName={gtmProductListName}
                    gtmMessageOrigin={gtmMessageOrigin}
                    listIndex={listIndex}
                />
            </div>
        </div>
    );
};
