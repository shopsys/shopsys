import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TIDs } from 'cypress/tids';
import type { Ref } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import type { ProductItemProps } from './ProductListItem';
import type { ProductListItemLayoutProps } from './ProductListItemActions';
import { ProductListItemAddToCart, ProductListItemButtons } from './ProductListItemActions';
import { ProductListItemImage } from './ProductListItemImage';

type ProductListItemGridViewProps = ProductListItemLayoutProps &
    Pick<ProductItemProps, 'size' | 'textSize' | 'textSizePrice'> & {
        forwardedRef: Ref<HTMLLIElement>;
    };

export const ProductListItemGridView: FC<ProductListItemGridViewProps> = ({
    allowKeyboardFocus,
    className,
    currentCart,
    forwardedRef,
    gtmMessageOrigin,
    gtmProductListName,
    highlightBadgeText,
    isProductInComparison,
    isProductInWishlist,
    listIndex,
    onProductClick,
    product,
    shouldShowProductActionSkeleton,
    size = 'large',
    textSize = 'sm',
    textSizePrice = 'lg',
    toggleProductInComparison,
    toggleProductInWishlist,
    visibleItemsConfig,
}) => {
    const { t } = useTranslation();

    return (
        <li
            data-tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
            ref={forwardedRef}
            className={twMergeCustom(
                'group relative flex select-text flex-col rounded-xl border border-background-more bg-background-more pt-10 pb-2.5 text-left transition sm:pb-5',
                size === 'small' && 'gap-0 pb-0',
                'hover:border-border-less hover:bg-background-default',
                highlightBadgeText && 'bg-primary-500/20 hover:border-primary-500',
                className,
            )}
        >
            {highlightBadgeText && (
                <div className="absolute top-5 left-2.5 z-above sm:left-5">
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
                onMouseUp={onProductClick}
            >
                <div
                    className={twMergeCustom(
                        'flex w-full flex-col gap-2.5 px-2.5 sm:px-5',
                        size === 'small' && 'py-4 pb-4',
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
                            className="min-h-10 xs:min-h-15 sm:min-h-10"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </div>
            </ExtendedNextLink>

            {visibleItemsConfig.productListButtons && (
                <div className="absolute top-1 right-0 flex justify-end sm:top-3 sm:right-2.5">
                    <ProductListItemButtons
                        allowKeyboardFocus={allowKeyboardFocus}
                        isProductInComparison={isProductInComparison}
                        isProductInWishlist={isProductInWishlist}
                        productName={product.fullName}
                        toggleProductInComparison={toggleProductInComparison}
                        toggleProductInWishlist={toggleProductInWishlist}
                    />
                </div>
            )}

            {visibleItemsConfig.addToCart && (
                <ProductListItemAddToCart
                    allowKeyboardFocus={allowKeyboardFocus}
                    currentCart={currentCart}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    listIndex={listIndex}
                    product={product}
                    productActionClassName="w-full px-2.5 pt-2.5 md:px-5"
                    shouldShowProductActionSkeleton={shouldShowProductActionSkeleton}
                    skeletonClassName="w-full px-2.5 pt-2.5 md:px-5"
                />
            )}
        </li>
    );
};
