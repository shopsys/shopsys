import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

type ProductComparisonItemProps = {
    product: TypeProductInProductListFragment;
    listIndex: number;
    stickyTriggerId?: string;
    toggleProductInComparison: () => void;
};

export const ProductComparisonHeadItem: FC<ProductComparisonItemProps> = ({
    product,
    listIndex,
    stickyTriggerId,
    toggleProductInComparison,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { isProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();
    const { canSeePrices } = useAuthorization();

    const onProductDetailRedirectHandler = (
        product: TypeListedProductFragment,
        listName: GtmProductListNameType,
        index: number,
    ) => {
        onGtmProductClickEventHandler(product, listName, index, url, !canSeePrices);
    };

    return (
        <th
            className="relative px-3 pb-3 align-top font-semibold sm:px-5 sm:pb-5"
            data-tid={TIDs.comparison_product_ + product.catalogNumber}
            id="js-table-compare-product"
        >
            <div className="flex w-45.5 flex-col gap-2 sm:w-51.25">
                <div className="flex flex-col gap-2">
                    <div className="grid grid-cols-1 grid-rows-[auto_auto_auto] gap-2">
                        <ExtendedNextLink
                            preventRedirectOnTextSelection
                            className="group/product-link col-start-1 row-start-1 row-end-4 grid grid-cols-1 grid-rows-subgrid text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden"
                            data-focus-color="preserve"
                            draggable={false}
                            href={product.slug}
                            type="product"
                            aria-label={t('Go to product page of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                            onClick={() =>
                                onProductDetailRedirectHandler(
                                    product,
                                    GtmProductListNameType.product_comparison_page,
                                    listIndex,
                                )
                            }
                        >
                            <div
                                className="row-start-1 flex h-46.25 w-full items-center justify-center pt-4 pb-3"
                                data-tid={TIDs.comparison_product_image}
                            >
                                <Image
                                    alt=""
                                    className="max-h-full w-auto"
                                    height={185}
                                    src={product.mainImage?.url}
                                    width={200}
                                />
                            </div>

                            <span
                                className="row-start-3 -mx-1 line-clamp-4 min-h-20 rounded-sm box-decoration-clone px-1 font-secondary text-sm group-hover/product-link:underline group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!"
                                id={stickyTriggerId}
                            >
                                {product.fullName}
                            </span>
                        </ExtendedNextLink>

                        <ProductListReviewsSummaryLink
                            className="relative z-above col-start-1 row-start-2 font-normal"
                            product={product}
                        />
                    </div>

                    <span className="text-xs">
                        {t('Code')}: {product.catalogNumber}
                    </span>
                </div>

                <ProductAction
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    gtmProductListName={GtmProductListNameType.product_comparison_page}
                    listIndex={listIndex}
                    product={product}
                />

                <div className="flex flex-col items-baseline gap-2 font-normal">
                    <ProductCompareButton
                        isProductInComparison={isProductInComparison(product.uuid)}
                        productName={product.fullName}
                        tabIndex={0}
                        isWithText
                        toggleProductInComparison={toggleProductInComparison}
                    />

                    <ProductWishlistButton
                        isWithText
                        isProductInWishlist={isProductInWishlist(product.uuid)}
                        productName={product.fullName}
                        tabIndex={0}
                        toggleProductInWishlist={() =>
                            toggleProductInWishlist(product, GtmProductListNameType.product_comparison_page, listIndex)
                        }
                    />
                </div>
            </div>

            <ProductFlags
                flags={product.flags}
                percentageDiscount={product.price.percentageDiscount}
                variant="comparison"
            />
        </th>
    );
};
