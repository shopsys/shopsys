import { ProductListItemImage } from './ProductListItemImage';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
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
    allowKeyboardFocus?: boolean;
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
            allowKeyboardFocus = true,
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { t } = useTranslation();
        const { canSeePrices } = useAuthorization();

        return (
            <li
                data-tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
                ref={ref}
                className={twMergeCustom(
                    'border-background-more bg-background-more group relative flex flex-col gap-2.5 rounded-xl border pb-5 text-left transition select-text',
                    size === 'small' && 'gap-0 py-2.5',
                    'hover:border-border-less hover:bg-background-default',
                    className,
                )}
            >
                <ExtendedNextLink
                    preventRedirectOnTextSelection
                    aria-label={t('Go to product page of {{ productName }}', { productName: product.fullName })}
                    className="text-text-default hover:text-link-default flex grow no-underline select-text hover:no-underline"
                    draggable={false}
                    href={product.slug}
                    tabIndex={allowKeyboardFocus ? 0 : -1}
                    title={t('Go to product page')}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    onMouseUp={() => {
                        onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices);
                        onClick?.(product, listIndex);
                    }}
                >
                    <div className="flex w-full flex-col gap-2.5 px-2.5 pt-5 sm:px-5">
                        <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                        <h3
                            className={twJoin(
                                'font-secondary group-hover:text-link-default grow overflow-hidden font-semibold break-words group-hover:underline',
                                textSize === 'xs' ? 'text-xs lg:text-xs' : 'text-sm lg:text-sm',
                            )}
                        >
                            {product.fullName}
                        </h3>

                        {product.__typename === 'MainVariant' && (
                            <div className="bg-background-default font-secondary group-hover:text-text-default flex w-fit items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs whitespace-nowrap">
                                <VariantIcon className="text-text-accent size-3" />
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
                                className="xs:min-h-[60px] min-h-10 sm:min-h-10"
                                isInquiryType={product.isInquiryType}
                            />
                        )}
                    </div>
                </ExtendedNextLink>

                <div className="flex w-full items-center justify-between gap-1 px-2.5 md:justify-normal md:gap-2.5 md:px-5">
                    {visibleItemsConfig.addToCart && (
                        <ProductAction
                            showResponsiveCartIcon
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                            product={product}
                            skipKeyboardNavigation={!allowKeyboardFocus}
                        />
                    )}

                    {visibleItemsConfig.productListButtons && (
                        <>
                            <ProductCompareButton
                                isProductInComparison={isProductInComparison}
                                productName={product.fullName}
                                tabIndex={allowKeyboardFocus ? 0 : -1}
                                toggleProductInComparison={toggleProductInComparison}
                            />
                            <ProductWishlistButton
                                isProductInWishlist={isProductInWishlist}
                                productName={product.fullName}
                                tabIndex={allowKeyboardFocus ? 0 : -1}
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
