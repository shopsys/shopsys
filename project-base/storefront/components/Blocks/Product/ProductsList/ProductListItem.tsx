import { ProductListItemImage } from './ProductListItemImage';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';
import { disableClickWhenTextSelected } from 'utils/ui/disableClickWhenTextSelected';

export type ProductVisibleItemsConfigType = {
    addToCart?: boolean;
    productListButtons?: boolean;
    storeAvailability?: boolean;
    price?: boolean;
    flags?: boolean;
    discount?: boolean;
    priceFromWord?: boolean;
};

export type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isProductInComparison: boolean;
    isProductInWishlist: boolean;
    toggleProductInComparison: () => void;
    toggleProductInWishlist: () => void;
    visibleItemsConfig?: ProductVisibleItemsConfigType;
    size?: 'extraSmall' | 'small' | 'medium' | 'large' | 'extraLarge';
    onClick?: (product: TypeListedProductFragment, index: number) => void;
    textSize?: 'xs' | 'sm';
    textSizePrice?: 'base' | 'lg';
} & FunctionComponentProps;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin,
            isProductInComparison,
            isProductInWishlist,
            toggleProductInComparison,
            toggleProductInWishlist,
            className,
            visibleItemsConfig = PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem,
            size = 'large',
            textSize = 'sm',
            textSizePrice = 'lg',
            onClick,
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { t } = useTranslation();
        const currentCustomerData = useCurrentCustomerData();

        return (
            <li
                ref={ref}
                tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
                className={twMergeCustom(
                    'group relative flex select-text flex-col gap-2.5 rounded-xl border border-backgroundMore bg-backgroundMore py-5 text-left transition',
                    size === 'small' && 'gap-0 py-2.5',
                    'hover:border-borderAccentLess hover:bg-background',
                    className,
                )}
            >
                <ExtendedNextLink
                    className="flex grow select-text text-text no-underline hover:text-link hover:no-underline"
                    draggable={false}
                    href={product.slug}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    onClickExtended={disableClickWhenTextSelected}
                    onMouseUp={() => {
                        onGtmProductClickEventHandler(
                            product,
                            gtmProductListName,
                            listIndex,
                            url,
                            !!currentCustomerData?.arePricesHidden,
                        );
                        onClick?.(product, listIndex);
                    }}
                >
                    <div className="flex flex-col gap-2.5 px-2.5 sm:px-5">
                        <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                        <div
                            className={twJoin(
                                'grow overflow-hidden break-words font-secondary font-semibold group-hover:text-link group-hover:underline',
                                textSize === 'xs' ? 'text-xs' : 'text-sm',
                            )}
                        >
                            {product.fullName}
                        </div>

                        {product.__typename === 'MainVariant' && (
                            <div className="flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background px-2.5 py-1.5 font-secondary text-xs group-hover:text-text">
                                <VariantIcon className="size-3 text-textAccent" />
                                {product.variantsCount} {t('variants count', { count: product.variantsCount })}
                            </div>
                        )}

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
                                className="min-h-10 xs:min-h-[60px] sm:min-h-10"
                                isInquiryType={product.isInquiryType}
                            />
                        )}
                    </div>
                </ExtendedNextLink>

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
                            <ProductCompareButton
                                isProductInComparison={isProductInComparison}
                                toggleProductInComparison={toggleProductInComparison}
                            />
                            <ProductWishlistButton
                                isProductInWishlist={isProductInWishlist}
                                toggleProductInWishlist={toggleProductInWishlist}
                            />
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
