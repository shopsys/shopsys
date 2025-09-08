import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListItemImage } from 'components/Blocks/Product/ProductsList/ProductListItemImage';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'next-translate/useTranslation';

type CategoryBestsellersListItemProps = {
    product: TypeListedProductFragment;
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
            aria-label={t('Go to bestseller product page of {{ productName }}', { productName: product.fullName })}
            className="hover:bg-background-default flex items-center justify-between gap-5 gap-y-4 rounded-md p-3 no-underline transition-colors hover:no-underline"
            draggable={false}
            href={productUrl}
            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
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

            <div className="flex w-full flex-col justify-between gap-x-4 gap-y-2.5 select-text md:flex-row md:items-center">
                <div className="font-secondary text-text-default line-clamp-5 max-w-80 flex-1 items-center text-sm font-semibold">
                    <ProductFlags
                        flags={product.flags}
                        percentageDiscount={product.price.percentageDiscount}
                        variant="bestsellers"
                    />

                    {product.fullName}
                </div>

                <ProductAvailability
                    availability={product.availability}
                    availableStoresCount={product.availableStoresCount}
                    className="md:basis-3/12"
                    isInquiryType={product.isInquiryType}
                />

                <ProductPrice className="md:basis-3/12 md:flex-col md:items-end" productPrice={product.price} />
            </div>
        </ExtendedNextLink>
    );
};
