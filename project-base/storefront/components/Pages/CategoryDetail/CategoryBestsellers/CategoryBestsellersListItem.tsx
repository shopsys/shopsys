import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListItemImage } from 'components/Blocks/Product/ProductsList/ProductListItemImage';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { disableClickWhenTextSelected } from 'utils/ui/disableClickWhenTextSelected';

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
    const { url } = useDomainConfig();
    const currentCustomerData = useCurrentCustomerData();

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    return (
        <ExtendedNextLink
            className="flex items-center justify-between gap-5 gap-y-4 p-3 no-underline transition-colors hover:bg-background hover:no-underline"
            draggable={false}
            href={productUrl}
            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
            onClickExtended={disableClickWhenTextSelected}
            onClick={() =>
                onGtmProductClickEventHandler(
                    product,
                    gtmProductListName,
                    listIndex,
                    url,
                    !!currentCustomerData?.arePricesHidden,
                )
            }
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
                <span className="line-clamp-5 max-w-80 flex-1 items-center font-secondary text-sm font-semibold text-text">
                    <ProductFlags
                        flags={product.flags}
                        percentageDiscount={product.price.percentageDiscount}
                        variant="bestsellers"
                    />
                    {product.fullName}
                </span>
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
