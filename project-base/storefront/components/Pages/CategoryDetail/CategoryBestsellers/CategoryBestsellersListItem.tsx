import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { AvailabilityStatusEnumApi, ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmProductListNameType } from 'gtm/types/enums';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { twJoin } from 'tailwind-merge';

type CategoryBestsellersListItemProps = {
    product: ListedProductFragmentApi;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'pages-category-bestseller-item-';

export const CategoryBestsellersListItem: FC<CategoryBestsellersListItemProps> = ({
    product,
    gtmProductListName,
    listIndex,
}) => {
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();

    return (
        <div className="flex flex-wrap items-center gap-y-4 border-t border-greyLight py-4 first-of-type:border-0 lg:flex-nowrap lg:gap-5">
            <div className="flex w-full items-center lg:basis-7/12">
                <div data-testid={TEST_IDENTIFIER + 'name'}>
                    <ExtendedNextLink
                        className="flex items-center gap-5 font-bold no-underline"
                        href={product.slug}
                        type="product"
                        onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
                    >
                        <Image
                            alt={product.fullName}
                            className="max-h-[80px] max-w-[80px]"
                            dataTestId={TEST_IDENTIFIER + 'image'}
                            image={product.mainImage}
                        />
                        <span>{product.fullName}</span>
                    </ExtendedNextLink>
                </div>
            </div>

            <div className="basis-4/6 lg:basis-3/12 lg:text-center">
                <span
                    className={twJoin(
                        product.availability.status === AvailabilityStatusEnumApi.InStockApi && 'text-inStock',
                        product.availability.status === AvailabilityStatusEnumApi.OutOfStockApi && 'text-red ',
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
