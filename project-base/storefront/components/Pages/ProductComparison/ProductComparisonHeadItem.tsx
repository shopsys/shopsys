import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { DragHandleIcon } from 'components/Basic/Icon/DragHandleIcon';
import { Image } from 'components/Basic/Image/Image';
import { SelectableCode } from 'components/Basic/SelectableCode/SelectableCode';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import { IconButton } from 'components/Forms/Button/IconButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { Reorder, useDragControls, useReducedMotion } from 'framer-motion';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

type ProductComparisonItemProps = {
    canReorder?: boolean;
    onReorderEnd?: () => void;
    onMove?: (direction: number) => void;
    product: TypeProductInProductListFragment;
    listIndex: number;
    columnIndex?: number;
    stickyTriggerId?: string;
    toggleProductInComparison: () => void;
};

export const ProductComparisonHeadItem: FC<ProductComparisonItemProps> = ({
    product,
    canReorder = false,
    onMove,
    onReorderEnd,
    listIndex,
    columnIndex,
    stickyTriggerId,
    toggleProductInComparison,
}) => {
    const { t } = useTranslation();
    const dragControls = useDragControls();
    const reducedMotion = useReducedMotion();
    const { url } = useDomainConfig();
    const { canSeePrices } = useAuthorization();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    const handleProductClick = () => {
        onGtmProductClickEventHandler(
            product,
            GtmProductListNameType.product_comparison_page,
            listIndex,
            url,
            !canSeePrices,
        );
    };

    return (
        <Reorder.Item
            as="div"
            value={product.uuid}
            dragListener={false}
            dragControls={dragControls}
            drag={canReorder ? 'x' : false}
            layout="position"
            initial={false}
            transition={reducedMotion ? { duration: 0 } : undefined}
            animate={{ boxShadow: '0 4px 16px rgba(0, 0, 0, 0)' }}
            onDragEnd={onReorderEnd}
            whileDrag={{
                boxShadow: '0 4px 16px rgba(0, 0, 0, 0.12)',
            }}
            className="group/comparison-product relative isolate row-span-8 grid min-w-0 grid-rows-subgrid gap-y-2 border-border-less border-l bg-background-default px-3 pt-4 pb-5 text-left font-normal max-md:nth-2:border-l-0 md:px-5"
            data-tid={TIDs.comparison_product_ + product.catalogNumber}
            data-comparison-product={columnIndex}
        >
            <div className="col-start-1 row-start-1 grid grid-cols-[minmax(0,1fr)_auto] items-start gap-1">
                <ProductFlags
                    flags={product.flags}
                    percentageDiscount={product.price.percentageDiscount}
                    variant="gridHeader"
                />
                <div className="col-start-2 -mt-2 -mr-2 flex items-center gap-1">
                    {canReorder && (
                        <IconButton
                            Icon={DragHandleIcon}
                            className="cursor-grab touch-none opacity-0 focus-visible:opacity-100 active:cursor-grabbing active:opacity-100 group-hover/comparison-product:opacity-100"
                            shape="rounded"
                            size="small"
                            variant="ghost"
                            title={t('Drag to reorder')}
                            tooltipLabel={t('Drag to reorder')}
                            ariaLabel={t('Reorder {{ productName }}. Use left and right arrow keys.', {
                                productName: product.fullName,
                            })}
                            onPointerDown={(event) => {
                                if (event.button !== 0) return;
                                event.currentTarget.focus({ preventScroll: true });
                                dragControls.start(event);
                            }}
                            onKeyDown={(event) => {
                                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                                    event.preventDefault();
                                    onMove?.(event.key === 'ArrowLeft' ? -1 : 1);
                                }
                            }}
                        />
                    )}
                    <ProductWishlistButton
                        isProductInWishlist={isProductInWishlist(product.uuid)}
                        productName={product.fullName}
                        toggleProductInWishlist={() =>
                            toggleProductInWishlist(product, GtmProductListNameType.product_comparison_page, listIndex)
                        }
                    />
                    <IconButton
                        Icon={CloseIcon}
                        ariaLabel={t('Remove from comparison product {{ productName }}', {
                            ns: 'accessibility',
                            productName: product.fullName,
                        })}
                        shape="rounded"
                        size="small"
                        tid={TIDs.product_compare_button}
                        title={t('Remove from comparison')}
                        tooltipLabel={t('Remove from comparison')}
                        variant="ghost"
                        onClick={toggleProductInComparison}
                    />
                </div>
            </div>

            <ExtendedNextLink
                preventRedirectOnTextSelection
                data-focus-color="preserve"
                className="group/product-link col-start-1 row-start-2 row-end-7 -mx-3 grid grid-rows-subgrid px-3 text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden md:-mx-5 md:px-5"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
                aria-label={t('Go to product page of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                onClick={handleProductClick}
            >
                <div
                    className="relative row-start-1 flex h-24 min-h-0 items-center justify-center md:h-28"
                    data-tid={TIDs.comparison_product_image}
                >
                    <Image
                        alt=""
                        className="max-h-full w-auto object-contain"
                        height={144}
                        src={product.mainImage?.url}
                        width={180}
                    />
                </div>
                <span
                    className="row-start-3 mb-3 line-clamp-3 font-secondary font-semibold text-sm leading-6 group-hover/product-link:underline md:line-clamp-2"
                    id={stickyTriggerId}
                >
                    <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                        {product.fullName}
                    </span>
                </span>
                <ProductPrice
                    className="row-start-4"
                    isPriceFromVisible
                    productPrice={product.price}
                    textPriceSize="base"
                />
                {!product.isSellingDenied && (
                    <ProductAvailability
                        className="row-start-5"
                        availability={product.availability}
                        availableStoresCount={null}
                        isInquiryType={product.isInquiryType}
                        isPersonalPickupOnly={product.isPersonalPickupOnly}
                    />
                )}
            </ExtendedNextLink>
            <ProductListReviewsSummaryLink
                className="relative z-above col-start-1 row-start-3 min-w-0"
                isReviewCountWrappedOnMobile
                product={product}
            />
            <div className="col-start-1 row-start-7 mt-4 flex flex-col justify-end">
                <ProductAction
                    buttonSize="small"
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    gtmProductListName={GtmProductListNameType.product_comparison_page}
                    listIndex={listIndex}
                    product={product}
                />
            </div>
            <SelectableCode
                className="col-start-1 row-start-8 truncate text-text-less text-xs"
                label={t('Code')}
                value={product.catalogNumber}
            />
        </Reorder.Item>
    );
};
