import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
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
                'group relative flex select-text flex-col rounded-xl border border-background-more bg-background-more pt-10 pb-2.5 text-left transition-[box-shadow,border-color,background-color,color] duration-200 ease-out pointer-fine:hover:shadow-[0_8px_18px_-12px_rgb(37_40_61/30%),0_2px_6px_-4px_rgb(37_40_61/16%)] sm:pb-5',
                size === 'small' && 'gap-0 pt-0 pb-0 sm:pb-0',
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
                className="group/product-link flex grow select-text rounded-xl text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden"
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
                <div
                    className={twMergeCustom(
                        'flex w-full flex-col gap-2.5 px-2.5 sm:px-5',
                        size === 'small' && 'py-4 pb-4',
                    )}
                >
                    <ProductListItemImage
                        imageCount={imageCount}
                        isWithImageGallery={isWithImageGallery}
                        product={product}
                        ref={productListItemImageRef}
                        size={size}
                        visibleItemsConfig={visibleItemsConfig}
                    />

                    <h3
                        className={twJoin(
                            'wrap-break-word grow overflow-hidden font-secondary font-semibold group-hover:text-text-default group-hover:underline',
                            textSize === 'xs' ? 'text-xs lg:text-xs' : 'text-sm lg:text-sm',
                        )}
                    >
                        <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                            {product.fullName}
                        </span>
                    </h3>

                    {product.__typename === 'MainVariant' && (
                        <div className="flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background-default px-2.5 py-1.5 font-secondary text-xs group-hover:text-text-default">
                            <VariantIcon className="size-4 text-text-accent" fill="currentColor" />
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
                            isPersonalPickupOnly={product.isPersonalPickupOnly}
                            className="min-h-10 xs:min-h-15 sm:min-h-10"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </div>
            </ExtendedNextLink>

            {isImageGalleryEnabled && (
                <ProductListItemGalleryControls
                    imageHeight={getProductListItemImageSize(size)}
                    onNext={() => productListItemImageRef.current?.selectNextImage()}
                    onPrepareGallery={() => productListItemImageRef.current?.prepareGallery()}
                    onPrevious={() => productListItemImageRef.current?.selectPreviousImage()}
                />
            )}

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
