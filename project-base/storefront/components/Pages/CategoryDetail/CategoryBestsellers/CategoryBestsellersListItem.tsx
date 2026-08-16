import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListItemImage } from 'components/Blocks/Product/ProductsList/ProductListItemImage';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeCategoryBestsellerFragment } from 'graphql/requests/categories/fragments/CategoryBestsellerFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type CategoryBestsellersListItemProps = {
    product: TypeCategoryBestsellerFragment;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
};

export const CategoryBestsellersListItem: FC<CategoryBestsellersListItemProps> = ({
    product,
    gtmProductListName,
    listIndex,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { canSeePrices } = useAuthorization();

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    const productLinkType = product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant';

    return (
        <div className="group relative grid grid-cols-[80px_minmax(0,1fr)] xxl:grid-cols-[80px_minmax(0,280px)_200px_minmax(240px,1fr)_auto] grid-rows-[1fr_auto_auto_0_1fr_auto_auto] gap-x-5 rounded-md p-3 transition-colors hover:bg-background-default has-[.product-list-reviews]:grid-rows-[1fr_auto_auto_auto_1fr_auto_auto] md:grid-cols-[80px_minmax(0,1fr)_minmax(0,1fr)_auto]">
            <ExtendedNextLink
                preventRedirectOnTextSelection
                className="group/product-link col-start-1 col-end-3 xxl:col-end-6 row-start-1 row-end-8 grid min-w-0 grid-cols-subgrid grid-rows-subgrid rounded-md text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden md:col-end-5"
                data-focus-color="preserve"
                draggable={false}
                href={productUrl}
                type={productLinkType}
                aria-label={t('Go to bestseller product page of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                onMouseUp={() =>
                    onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices)
                }
            >
                <div className="col-start-1 row-start-1 row-end-6 flex w-20 shrink-0 items-center">
                    <ProductListItemImage
                        product={product}
                        size="extraSmall"
                        tid={TIDs.category_bestseller_image}
                        visibleItemsConfig={{ flags: false }}
                    />
                </div>

                <div className="col-start-2 row-start-2 min-w-0 empty:hidden xl:max-w-80">
                    <ProductFlags
                        flags={product.flags}
                        percentageDiscount={product.price.percentageDiscount}
                        variant="list"
                    />
                </div>

                <h3 className="wrap-break-word col-start-2 row-start-3 mt-1.5 overflow-hidden font-secondary font-semibold text-sm group-hover:text-text-default group-hover:underline xl:max-w-80">
                    <span className="group-hover:underline group-focus-visible/product-link:underline">
                        {product.fullName}
                    </span>
                </h3>

                <ProductAvailability
                    availability={product.availability}
                    availableStoresCount={product.availableStoresCount}
                    className="col-start-2 xxl:col-start-4 row-start-6 mt-2.5 md:col-start-3 md:row-start-1 md:row-end-6 md:mt-0 md:self-center md:justify-self-start min-[1380px]:whitespace-nowrap"
                    isInquiryType={product.isInquiryType}
                />

                <ProductPrice
                    className="col-start-2 xxl:col-start-5 row-start-7 mt-2.5 md:col-start-4 md:row-start-1 md:row-end-6 md:mt-0 md:flex-col md:items-end md:self-center"
                    productPrice={product.price}
                />
            </ExtendedNextLink>

            <ProductListReviewsSummaryLink
                className="product-list-reviews relative z-above col-start-2 xxl:col-start-3 row-start-4 xxl:row-start-1 xxl:row-end-6 mt-1.5 xxl:mt-0 min-w-0 xxl:self-center"
                linkType={productLinkType}
                product={product}
                productUrl={productUrl}
            />
        </div>
    );
};
