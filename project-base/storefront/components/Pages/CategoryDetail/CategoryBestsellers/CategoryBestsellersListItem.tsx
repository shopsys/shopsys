import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

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

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    return (
        <div className="flex flex-wrap items-center gap-y-4 border-t border-greyLight py-4 first-of-type:border-0 lg:flex-nowrap lg:gap-5">
            <ExtendedNextLink
                className="flex items-center gap-5 font-bold no-underline lg:flex-1"
                href={productUrl}
                type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
                onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
            >
                <div className="flex w-20 shrink-0 items-center justify-center">
                    <Image
                        alt={product.mainImage?.name || product.fullName}
                        className="max-h-20 w-auto shrink-0"
                        height={80}
                        src={product.mainImage?.url}
                        width={80}
                    />
                </div>
                <span>{product.fullName}</span>
            </ExtendedNextLink>

            <div className="basis-4/6 lg:basis-3/12 lg:text-center">
                <span
                    className={twJoin(
                        product.availability.status === TypeAvailabilityStatusEnum.InStock && 'text-inStock',
                        product.availability.status === TypeAvailabilityStatusEnum.OutOfStock && 'text-red ',
                    )}
                >
                    {product.availability.name}
                </span>

                <ProductAvailableStoresCount
                    availableStoresCount={product.availableStoresCount}
                    isMainVariant={product.isMainVariant}
                />
            </div>

            <div className="basis-2/6 text-right font-bold leading-5 text-primary lg:basis-2/12">
                {formatPrice(product.price.priceWithVat)}
            </div>
        </div>
    );
};
