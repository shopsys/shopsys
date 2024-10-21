import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductListItemImage } from 'components/Blocks/Product/ProductsList/ProductListItemImage';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

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
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const currentCustomerData = useCurrentCustomerData();

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    return (
        <ExtendedNextLink
            href={productUrl}
            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
            className={twJoin(
                'group flex items-center justify-between gap-5 gap-y-4 p-3 no-underline transition-colors hover:bg-background hover:no-underline',
            )}
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
            <div className="flex w-full flex-col justify-between gap-x-4 gap-y-2.5 md:flex-row md:items-center">
                <span className="line-clamp-5 max-w-80 flex-1 items-center font-secondary text-sm font-semibold text-text">
                    {!!product.flags.length && <ProductFlags flags={product.flags} variant="bestsellers" />}
                    {product.fullName}
                </span>

                <div className="max-w-48">
                    <ProductAvailableStoresCount
                        availableStoresCount={product.availableStoresCount}
                        isMainVariant={product.isMainVariant}
                        name={product.availability.name}
                    />
                </div>

                <div className="basis-2/6 font-bold leading-5 text-price md:basis-2/12 md:text-right">
                    {isPriceVisible(product.price.priceWithVat) && formatPrice(product.price.priceWithVat)}
                </div>
            </div>
        </ExtendedNextLink>
    );
};
