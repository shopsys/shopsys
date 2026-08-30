import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListItemImage } from 'components/Blocks/Product/ProductsList/ProductListItemImage';
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

    return (
        <ExtendedNextLink
            preventRedirectOnTextSelection
            className="group flex items-center justify-between gap-5 gap-y-4 rounded-md p-3 no-underline transition-colors hover:bg-background-default hover:no-underline"
            draggable={false}
            href={productUrl}
            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
            aria-label={t('Go to bestseller product page of {{ productName }}', {
                ns: 'accessibility',
                productName: product.fullName,
            })}
            onMouseUp={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices)}
        >
            <div className="flex w-20 shrink-0">
                <ProductListItemImage
                    product={product}
                    size="extraSmall"
                    tid={TIDs.category_bestseller_image}
                    visibleItemsConfig={{ flags: false }}
                />
            </div>

            <div className="flex w-full select-text flex-col justify-between gap-x-4 gap-y-2.5 md:flex-row md:items-center">
                <div className="line-clamp-5 max-w-80 flex-1 items-center font-secondary font-semibold text-sm text-text-default">
                    <ProductFlags
                        flags={product.flags}
                        percentageDiscount={product.price.percentageDiscount}
                        variant="bestsellers"
                    />

                    <span className="group-hover:underline group-focus-visible:underline">{product.fullName}</span>
                </div>

                <ProductAvailability
                    availability={product.availability}
                    availableStoresCount={product.availableStoresCount}
                    isPersonalPickupOnly={product.isPersonalPickupOnly}
                    className="md:basis-3/12 min-[1380px]:w-52 min-[1380px]:shrink-0 min-[1380px]:basis-auto"
                    isInquiryType={product.isInquiryType}
                />

                <ProductPrice className="md:basis-3/12 md:flex-col md:items-end" productPrice={product.price} />
            </div>
        </ExtendedNextLink>
    );
};
