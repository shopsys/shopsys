import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
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
import { forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { ProductListItemImage } from './ProductListItemImage';

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
    highlightBadgeText?: string;
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
            highlightBadgeText,
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
                    'group relative flex select-text flex-col gap-2.5 rounded-xl border border-background-more bg-background-more pb-5 text-left transition',
                    size === 'small' && 'gap-0 pb-0',
                    'hover:border-border-less hover:bg-background-default',
                    highlightBadgeText && 'bg-primary-500/20 hover:border-primary-500',
                    className,
                )}
            >
                {highlightBadgeText && (
                    <div className="absolute top-5 right-2.5 z-above items-end sm:right-5">
                        <Flag type="highlight">{highlightBadgeText}</Flag>
                    </div>
                )}

                <ExtendedNextLink
                    preventRedirectOnTextSelection
                    className="flex grow select-text rounded-xl text-text-default no-underline hover:text-link-default hover:no-underline"
                    draggable={false}
                    href={product.slug}
                    tabIndex={allowKeyboardFocus ? 0 : -1}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    aria-label={t('Go to product page of {{ productName }}', {
                        ns: 'accessibility',
                        productName: product.fullName,
                    })}
                    onMouseUp={() => {
                        onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices);
                        onClick?.(product, listIndex);
                    }}
                >
                    <div
                        className={twMergeCustom(
                            'flex w-full flex-col gap-2.5 px-2.5 pt-5 sm:px-5',
                            size === 'small' && 'py-4 pt-4 pb-4',
                        )}
                    >
                        <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                        <h3
                            className={twJoin(
                                'wrap-break-word grow overflow-hidden font-secondary font-semibold group-hover:text-link-default group-hover:underline',
                                textSize === 'xs' ? 'text-xs lg:text-xs' : 'text-sm lg:text-sm',
                            )}
                        >
                            {product.fullName}
                        </h3>

                        {product.__typename === 'MainVariant' && (
                            <div className="flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background-default px-2.5 py-1.5 font-secondary text-xs group-hover:text-text-default">
                                <VariantIcon className="size-3 text-text-accent" />
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
