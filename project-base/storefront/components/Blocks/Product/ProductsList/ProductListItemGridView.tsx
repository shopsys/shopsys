import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import { TIDs } from 'cypress/tids';
import { type Ref, useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import type { ProductItemProps } from './ProductListItem';
import type { ProductListItemLayoutProps } from './ProductListItemActions';
import { ProductListItemAddToCart, ProductListItemButtons } from './ProductListItemActions';
import { ProductListItemGalleryControls } from './ProductListItemGalleryControls';
import {
    getProductListItemImageSize,
    ProductListItemImage,
    type ProductListItemImageHandle,
} from './ProductListItemImage';

type ProductListItemGridViewProps = ProductListItemLayoutProps &
    Pick<ProductItemProps, 'imageCount' | 'isWithImageGallery' | 'size' | 'textSize' | 'textSizePrice'> & {
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
    imageCount,
    isProductInComparison,
    isProductInWishlist,
    isWithImageGallery = false,
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
    const productListItemImageRef = useRef<ProductListItemImageHandle>(null);
    const isImageGalleryEnabled = isWithImageGallery && (imageCount ?? 0) > 1;

    return (
        <li
            data-tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
            ref={forwardedRef}
            className={twMergeCustom(
                'group relative flex select-text flex-col rounded-xl border border-background-more bg-background-more px-2.5 pt-4 pb-2.5 text-left transition-[box-shadow,border-color,background-color,color] duration-200 ease-out pointer-fine:hover:shadow-[0_8px_18px_-12px_rgb(37_40_61/30%),0_2px_6px_-4px_rgb(37_40_61/16%)] sm:px-5 sm:pb-5',
                size === 'small' && 'pb-4 sm:pb-4',
                'hover:border-border-less hover:bg-background-default',
                highlightBadgeText && 'bg-primary-500/20 hover:border-primary-500',
                className,
            )}
        >
            {(highlightBadgeText ||
                visibleItemsConfig.productListButtons ||
                visibleItemsConfig.flags ||
                visibleItemsConfig.discount) && (
                <div className="relative z-above grid grid-cols-[minmax(0,1fr)_auto] items-start gap-1">
                    <div className="relative min-w-0">
                        <div className="absolute inset-x-0 top-0 flex flex-col items-start gap-1">
                            {highlightBadgeText && (
                                <Flag className="max-w-full" type="highlight">
                                    <span className="wrap-break-word line-clamp-2">{highlightBadgeText}</span>
                                </Flag>
                            )}

                            <ProductFlags
                                flags={product.flags}
                                percentageDiscount={product.price.percentageDiscount}
                                variant="gridHeader"
                                visibleItemsConfig={visibleItemsConfig}
                            />
                        </div>
                    </div>

                    {visibleItemsConfig.productListButtons && (
                        <div className="col-start-2 -mt-2 -mr-2 flex shrink-0 items-center">
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
                </div>
            )}

            <div className="grid grow grid-cols-1 grid-rows-[auto_auto_minmax(0,1fr)_auto_auto_auto_auto]">
                <ExtendedNextLink
                    preventRedirectOnTextSelection
                    className="group/product-link col-start-1 row-start-1 row-end-7 -mx-2.5 grid select-text grid-cols-1 grid-rows-subgrid rounded-xl px-2.5 text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden sm:-mx-5 sm:px-5"
                    data-focus-color="preserve"
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
                    <div className="row-start-1">
                        <ProductListItemImage
                            imageCount={imageCount}
                            isProductFlagsVisible={false}
                            isWithImageGallery={isWithImageGallery}
                            product={product}
                            ref={productListItemImageRef}
                            size={size}
                            visibleItemsConfig={visibleItemsConfig}
                        />
                    </div>

                    <h3
                        className={twJoin(
                            'wrap-break-word row-start-3 mt-2.5 overflow-hidden font-secondary font-semibold group-hover:text-text-default group-hover:underline',
                            textSize === 'xs' ? 'text-xs lg:text-xs' : 'text-sm lg:text-sm',
                        )}
                    >
                        <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                            {product.fullName}
                        </span>
                    </h3>

                    {product.__typename === 'MainVariant' && (
                        <div className="row-start-4 mt-2.5 flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background-default px-2.5 py-1.5 font-secondary text-xs group-hover:text-text-default">
                            <VariantIcon className="size-4 text-text-accent" fill="currentColor" />
                            {product.variantsCount} {t('variants count', { count: product.variantsCount })}
                        </div>
                    )}

                    {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                        <ProductPrice
                            className="row-start-5 mt-2.5 min-h-6 sm:min-h-7"
                            isPriceFromVisible={visibleItemsConfig.priceFromWord}
                            productPrice={product.price}
                            textPriceSize={textSizePrice}
                        />
                    )}

                    {visibleItemsConfig.storeAvailability && !product.isSellingDenied && (
                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            className="row-start-6 mt-2.5 min-h-10 xs:min-h-15 sm:min-h-10"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </ExtendedNextLink>

                {visibleItemsConfig.reviews && (
                    <ProductListReviewsSummaryLink
                        allowKeyboardFocus={allowKeyboardFocus}
                        className="relative z-above col-start-1 row-start-2 mt-2.5 min-w-0"
                        isReviewCountWrappedOnMobile
                        product={product}
                    />
                )}

                {isImageGalleryEnabled && (
                    <ProductListItemGalleryControls
                        imageHeight={getProductListItemImageSize(size)}
                        onNext={() => productListItemImageRef.current?.selectNextImage()}
                        onPrepareGallery={() => productListItemImageRef.current?.prepareGallery()}
                        onPrevious={() => productListItemImageRef.current?.selectPreviousImage()}
                    />
                )}

                {visibleItemsConfig.addToCart && (
                    <ProductListItemAddToCart
                        allowKeyboardFocus={allowKeyboardFocus}
                        currentCart={currentCart}
                        gtmMessageOrigin={gtmMessageOrigin}
                        gtmProductListName={gtmProductListName}
                        listIndex={listIndex}
                        product={product}
                        productActionClassName="col-start-1 row-start-7 mt-2.5 w-full"
                        shouldShowProductActionSkeleton={shouldShowProductActionSkeleton}
                        skeletonClassName="col-start-1 row-start-7 mt-2.5 w-full"
                    />
                )}
            </div>
        </li>
    );
};
