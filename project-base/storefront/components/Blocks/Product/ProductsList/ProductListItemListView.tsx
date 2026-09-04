import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import { TIDs } from 'cypress/tids';
import type { MouseEvent, Ref } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import type { ProductListItemLayoutProps } from './ProductListItemActions';
import { ProductListItemAddToCart, ProductListItemButtons } from './ProductListItemActions';
import { ProductListItemImage } from './ProductListItemImage';

type ProductListItemListViewProps = ProductListItemLayoutProps & {
    forwardedRef: Ref<HTMLLIElement>;
};

const preventProductActionTextSelection = (event: MouseEvent<HTMLDivElement>) => {
    if (event.target instanceof HTMLInputElement) {
        return;
    }

    event.preventDefault();
    window.getSelection()?.removeAllRanges();
};

export const ProductListItemListView: FC<ProductListItemListViewProps> = ({
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
                'group relative grid select-text gap-2 rounded-xl border border-background-more bg-background-more p-3 text-left transition-[box-shadow,border-color,background-color,color] duration-200 ease-out pointer-fine:hover:shadow-[0_8px_18px_-12px_rgb(37_40_61/30%),0_2px_6px_-4px_rgb(37_40_61/16%)]',
                'hover:border-border-less hover:bg-background-default',
                'sm:p-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center',
                highlightBadgeText && 'bg-primary-500/20 hover:border-primary-500',
                className,
            )}
        >
            {highlightBadgeText && (
                <div className="absolute top-3 right-3 z-above items-end">
                    <Flag type="highlight">{highlightBadgeText}</Flag>
                </div>
            )}

            <div className="grid xxl:w-full min-w-0 grid-cols-[80px_minmax(0,1fr)] xxl:grid-cols-[88px_minmax(0,280px)_200px_minmax(240px,1fr)] grid-rows-[auto_auto_0_auto_auto_auto] content-center gap-x-3 rounded-md has-[.product-list-reviews]:grid-rows-[auto_auto_auto_auto_auto_auto] xl:w-fit xl:grid-cols-[88px_minmax(0,280px)_minmax(0,240px)] min-[1380px]:grid-cols-[88px_minmax(0,280px)_max-content]">
                <ExtendedNextLink
                    preventRedirectOnTextSelection
                    className="group/product-link col-start-1 col-end-3 xxl:col-end-5 row-start-1 row-end-7 grid min-w-0 grid-cols-subgrid grid-rows-subgrid rounded-md text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden xl:col-end-4"
                    data-focus-color="preserve"
                    draggable={false}
                    href={product.slug}
                    tabIndex={allowKeyboardFocus ? 0 : -1}
                    title={t('Go to product page')}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    aria-label={t('Go to product page of {{ productName }}', {
                        ns: 'accessibility',
                        productName: product.fullName,
                    })}
                    onMouseUp={onProductClick}
                >
                    <div className="col-start-1 row-start-1 row-end-6 flex items-center justify-center xl:relative xl:block xl:min-h-0">
                        <div className="flex items-center justify-center xl:absolute xl:inset-0">
                            <ProductListItemImage
                                product={product}
                                size="extraSmall"
                                visibleItemsConfig={{ ...visibleItemsConfig, discount: false, flags: false }}
                            />
                        </div>
                    </div>

                    <div className="col-start-2 row-start-1 min-w-0 empty:hidden xl:max-w-70">
                        <ProductFlags
                            flags={product.flags}
                            percentageDiscount={product.price.percentageDiscount}
                            variant="list"
                            visibleItemsConfig={visibleItemsConfig}
                        />
                    </div>

                    <h3 className="wrap-break-word col-start-2 row-start-2 mt-1.5 overflow-hidden font-secondary font-semibold text-sm group-hover:text-text-default group-hover:underline xl:max-w-70">
                        <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                            {product.fullName}
                        </span>
                    </h3>

                    <div className="col-start-2 row-start-4 mt-1.5 text-text-less text-xs">
                        {t('Code')}: {product.catalogNumber}
                    </div>

                    {product.__typename === 'MainVariant' && (
                        <div className="col-start-2 row-start-5 mt-1 flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background-default px-2.5 py-1.5 font-secondary text-xs group-hover:text-text-default">
                            <VariantIcon className="size-3 text-text-accent" />
                            {product.variantsCount} {t('variants count', { count: product.variantsCount })}
                        </div>
                    )}

                    {visibleItemsConfig.storeAvailability && !product.isSellingDenied && (
                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            className="col-span-2 xxl:col-start-4 row-start-6 mt-2 min-h-0 text-sm leading-5 xl:col-span-1 xl:col-start-3 xl:row-start-1 xl:row-end-6 xl:mt-0 xl:max-w-70 xl:self-center xl:justify-self-start min-[1380px]:max-w-none min-[1380px]:whitespace-nowrap"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </ExtendedNextLink>

                {visibleItemsConfig.reviews && (
                    <ProductListReviewsSummaryLink
                        allowKeyboardFocus={allowKeyboardFocus}
                        className="product-list-reviews relative z-above col-start-2 xxl:col-start-3 row-start-3 xxl:row-start-1 xxl:row-end-6 mt-1.5 xxl:mt-0 min-w-0 xxl:self-center"
                        product={product}
                    />
                )}
            </div>

            <div className="flex min-w-0 flex-col items-stretch gap-2 sm:flex-row sm:items-end sm:justify-between md:flex-col md:items-end md:justify-center">
                {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                    <ProductPrice
                        className="justify-end text-right"
                        isPriceFromVisible={visibleItemsConfig.priceFromWord}
                        productPrice={product.price}
                        textPriceSize="lg"
                    />
                )}

                <div
                    className="ml-auto flex flex-wrap items-center justify-end gap-2"
                    onMouseDownCapture={preventProductActionTextSelection}
                >
                    {visibleItemsConfig.productListButtons && (
                        <div className="flex shrink-0 items-center">
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
                            buttonSize="medium"
                            currentCart={currentCart}
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                            product={product}
                            productActionClassName="w-40 shrink-0 sm:w-44"
                            shouldShowProductActionSkeleton={shouldShowProductActionSkeleton}
                            skeletonClassName="h-9 w-40 shrink-0 sm:w-44"
                        />
                    )}
                </div>
            </div>
        </li>
    );
};
