import { ProductWishlistButton } from 'app/_components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'app/_components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'app/_components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'app/_components/Blocks/Product/ProductPrice';
import { ProductListItemImage } from 'app/_components/Blocks/Product/ProductsList/ProductListItemImage';
import { ProductListItemInfo } from 'app/_components/Blocks/Product/ProductsList/ProductListItemInfo';
import { ProductListItemWrapper } from 'app/_components/Blocks/Product/ProductsList/ProductListItemWrapper';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { forwardRef } from 'react';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';

export type ProductVisibleItemsConfigType = {
    addToCart?: boolean;
    productListButtons?: boolean;
    storeAvailability?: boolean;
    price?: boolean;
    flags?: boolean;
    discount?: boolean;
    priceFromWord?: boolean;
    wishlistUuid?: string;
    index?: number;
};

export type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    visibleItemsConfig?: ProductVisibleItemsConfigType;
    size?: 'extraSmall' | 'small' | 'medium' | 'large' | 'extraLarge';
    textSize?: 'xs' | 'sm';
    textSizePrice?: 'base' | 'lg';
    isShownInSlider?: boolean;
} & FunctionComponentProps;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin,
            className,
            visibleItemsConfig = PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem,
            size = 'large',
            textSize = 'sm',
            textSizePrice = 'lg',
            isShownInSlider = false,
        },
        ref,
    ) => {
        return (
            <li
                ref={ref}
                tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
                className={twMergeCustom(
                    'group border-backgroundMore bg-backgroundMore hover:border-borderAccentLess hover:bg-background relative flex flex-col gap-2.5 rounded-xl border py-5 text-left transition select-text',
                    size === 'small' && 'gap-0 py-2.5',
                    isShownInSlider && 'mr-2 snap-center last:mr-0 md:mr-4 md:snap-start',
                    className,
                )}
            >
                <ProductListItemWrapper gtmProductListName={gtmProductListName} listIndex={listIndex} product={product}>
                    <div className="flex flex-col gap-2.5 px-2.5 sm:px-5">
                        <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                        <ProductListItemInfo
                            fullName={product.fullName}
                            textSize={textSize}
                            typename={product.__typename}
                            variantsCount={product.__typename === 'MainVariant' ? product.variantsCount : 0}
                        />

                        {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                            <ProductPrice
                                className="min-h-6 sm:min-h-7"
                                isPriceFromVisible={visibleItemsConfig.priceFromWord}
                                productPrice={product.price}
                                textPriceSize={textSizePrice}
                            />
                        )}

                        {visibleItemsConfig.storeAvailability && !product.isSellingDenied && (
                            <ProductAvailability
                                availability={product.availability}
                                availableStoresCount={product.availableStoresCount}
                                className="xs:min-h-[60px] min-h-10 sm:min-h-10"
                                isInquiryType={product.isInquiryType}
                            />
                        )}
                    </div>
                </ProductListItemWrapper>

                <div className="flex w-full items-center justify-between gap-1 px-2.5 sm:justify-normal sm:gap-2.5 sm:px-5">
                    {visibleItemsConfig.addToCart && (
                        <ProductAction
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                            product={product}
                        />
                    )}

                    {visibleItemsConfig.productListButtons && (
                        <>
                            {/* <ProductCompareButton
                                isProductInComparison={product.listState.isInComparison}
                                // toggleProductInComparison={toggleProductInComparison}
                            /> */}
                            <ProductWishlistButton productUuid={product.uuid} />
                        </>
                    )}
                </div>
            </li>
        );
    },
);

ProductListItem.displayName = 'ProductItem';

export const PREDEFINED_VISIBLE_ITEMS_CONFIGS = {
    largeItem: {
        productListButtons: true,
        addToCart: true,
        flags: true,
        discount: false,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
    } as ProductVisibleItemsConfigType,
    mediumItem: {
        flags: true,
        discount: false,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
    } as ProductVisibleItemsConfigType,
} as const;
