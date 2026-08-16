import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import { ProductActionSkeleton } from 'components/Blocks/Skeleton/ProductActionSkeleton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageReadyEvents/productList/useGtmSliderProductListViewEvent';
import { useMemo } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isProductSellable } from 'utils/product/isProductSellable';
import { generateProductImageAlt } from 'utils/productAltText';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { twMergeCustom } from 'utils/twMerge';
import { isTextSelected } from 'utils/ui/isTextSelected';

type ArticleProductHeroProps = {
    product: TypeListedProductFragment;
};

const HERO_LIST_INDEX = 0;
const HERO_IMAGE_SIZE = 240;

export const ArticleProductHero: FC<ArticleProductHeroProps> = ({ product }) => {
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const { canCreateOrder, canSeePrices } = useAuthorization();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const { isProductInComparison, toggleProductInComparison } = useComparison();
    const { isProductInWishlist, toggleProductInWishlist } = useWishlist();
    const products = useMemo(() => [product], [product]);

    useGtmSliderProductListViewEvent(products, GtmProductListNameType.other);

    const isProductActionDependentOnCart = canCreateOrder && isProductSellable(product);
    const shouldShowProductActionSkeleton = isProductActionDependentOnCart && isCartFetchingOrUnavailable;
    const handleProductClick = () => {
        if (isTextSelected()) {
            return;
        }

        onGtmProductClickEventHandler(product, GtmProductListNameType.other, HERO_LIST_INDEX, url, !canSeePrices);
    };

    return (
        <article
            className={twMergeCustom(
                'group relative grid grid-cols-[minmax(0,1fr)_auto] grid-rows-[repeat(4,auto)_0_repeat(3,auto)] gap-x-5 vl:gap-x-8 rounded-2xl border border-transparent bg-background-more p-5 text-left transition hover:border-border-less hover:bg-background-default md:grid-cols-[240px_minmax(0,1fr)_auto] md:grid-rows-[repeat(3,auto)_0_repeat(3,auto)]',
                product.__typename === 'MainVariant' && 'grid-rows-[repeat(8,auto)] md:grid-rows-[repeat(7,auto)]',
            )}
            data-product-catnum={product.catalogNumber}
            data-tid={TIDs.grapesjs_product_hero}
        >
            <ExtendedNextLink
                preventRedirectOnTextSelection
                className="group/product-link col-span-2 col-start-1 row-span-8 row-start-1 grid select-text grid-cols-subgrid grid-rows-subgrid text-text-default no-underline hover:no-underline focus-visible:outline-hidden md:col-span-3 md:col-start-1 md:row-span-7 md:row-start-1"
                data-focus-color="preserve"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
                aria-label={t('Go to product page of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                onMouseUp={handleProductClick}
            >
                <div
                    data-tid={TIDs.product_list_item_image}
                    className="col-span-2 col-start-1 row-start-1 mt-6 mb-5 flex items-center justify-center md:col-span-1 md:col-start-1 md:row-span-7 md:row-start-1 md:mb-0 md:pt-0"
                >
                    <Image
                        alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                        className="h-55 w-55 object-contain mix-blend-multiply"
                        draggable={false}
                        height={HERO_IMAGE_SIZE}
                        src={product.mainImage?.url}
                        width={HERO_IMAGE_SIZE}
                    />
                </div>

                <div className="col-span-2 col-start-1 row-span-6 row-start-2 grid min-w-0 grid-cols-subgrid grid-rows-subgrid md:col-start-2 md:row-start-1">
                    <div className="col-span-2 col-start-1 row-start-1 mb-2 flex min-w-0 flex-wrap items-center gap-2 md:col-span-1 md:col-start-1">
                        <Flag type="highlight" className="mb-1">
                            {t('Recommended product')}
                        </Flag>

                        <ProductFlags
                            flags={product.flags}
                            percentageDiscount={product.price.percentageDiscount}
                            variant="bestsellers"
                            visibleItemsConfig={{ flags: true, discount: false }}
                        />
                    </div>

                    <h3 className="wrap-break-word col-span-2 col-start-1 row-start-2 mb-2 overflow-hidden font-secondary font-semibold text-lg leading-tight group-hover:text-text-default group-hover:underline sm:text-xl">
                        <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                            {product.fullName}
                        </span>
                    </h3>

                    {product.__typename === 'MainVariant' && (
                        <div className="col-span-2 col-start-1 row-start-4 mb-2 flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background-default px-2.5 py-1.5 font-secondary text-xs group-hover:text-text-default">
                            <VariantIcon className="size-3 text-text-accent" />
                            {product.variantsCount} {t('variants count', { count: product.variantsCount })}
                        </div>
                    )}

                    {!(product.isMainVariant && product.isSellingDenied) && (
                        <ProductPrice
                            className="col-span-2 col-start-1 row-start-5 mb-2 min-h-7"
                            isPriceFromVisible
                            productPrice={product.price}
                            textPriceSize="lg"
                        />
                    )}

                    {!product.isSellingDenied && (
                        <ProductAvailability
                            className="col-span-2 col-start-1 row-start-6 mb-2"
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            isPersonalPickupOnly={product.isPersonalPickupOnly}
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </div>
            </ExtendedNextLink>

            <ProductListReviewsSummaryLink
                className="relative z-above col-span-2 col-start-1 row-start-4 mb-2 min-w-0 md:col-start-2 md:row-start-3"
                product={product}
            />

            <div className="absolute top-3 right-3 z-above flex shrink-0 items-center self-start justify-self-end md:relative md:top-auto md:right-auto md:col-start-3 md:row-start-1 md:-mt-2 md:-mr-2">
                <ProductCompareButton
                    isProductInComparison={isProductInComparison(product.uuid)}
                    productName={product.fullName}
                    toggleProductInComparison={() =>
                        toggleProductInComparison(product, GtmProductListNameType.other, HERO_LIST_INDEX)
                    }
                />
                <ProductWishlistButton
                    isProductInWishlist={isProductInWishlist(product.uuid)}
                    productName={product.fullName}
                    toggleProductInWishlist={() =>
                        toggleProductInWishlist(product, GtmProductListNameType.other, HERO_LIST_INDEX)
                    }
                />
            </div>

            <div className="relative z-above col-span-2 col-start-1 row-start-8 w-full max-w-60 md:col-start-2 md:row-start-7">
                {shouldShowProductActionSkeleton ? (
                    <ProductActionSkeleton className="w-full" isWithAddToCart isWithProductListButtons={false} />
                ) : (
                    <ProductAction
                        currentCart={{ cart, isCartFetchingOrUnavailable }}
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        gtmProductListName={GtmProductListNameType.other}
                        listIndex={HERO_LIST_INDEX}
                        product={product}
                        buttonSize="large"
                    />
                )}
            </div>
        </article>
    );
};
