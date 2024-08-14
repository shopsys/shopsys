import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
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
        <ExtendedNextLink
            href={productUrl}
            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
            className={twJoin(
                'flex flex-wrap items-center gap-y-4 p-4 rounded lg:flex-nowrap lg:gap-5 transition-colors no-underline hover:no-underline',
                'bg-backgroundMore',
                'hover:bg-backgroundMost',
            )}
            onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
        >
            <div className="flex items-center gap-5 font-bold no-underline hover:no-underline lg:flex-1">
                <div className="flex w-20 shrink-0 items-center justify-center">
                    <Image
                        alt={product.mainImage?.name || product.fullName}
                        className="max-h-20 w-auto shrink-0"
                        height={80}
                        src={product.mainImage?.url}
                        tid={TIDs.category_bestseller_image}
                        width={80}
                    />
                </div>
                <span>{product.fullName}</span>
            </div>

            <div className="basis-4/6 lg:basis-3/12 lg:text-center">
                <span
                    className={twJoin(
                        product.availability.status === TypeAvailabilityStatusEnum.InStock &&
                            'text-availabilityInStock',
                        product.availability.status === TypeAvailabilityStatusEnum.OutOfStock &&
                            'text-availabilityOutOfStock',
                    )}
                >
                    {product.availability.name}
                </span>

                <ProductAvailableStoresCount
                    availableStoresCount={product.availableStoresCount}
                    isMainVariant={product.isMainVariant}
                />
            </div>

            <div className="basis-2/6 text-right font-bold leading-5 text-price lg:basis-2/12">
                {formatPrice(product.price.priceWithVat)}
            </div>
        </ExtendedNextLink>
    );
};
