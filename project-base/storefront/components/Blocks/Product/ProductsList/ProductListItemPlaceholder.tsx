import { PREDEFINED_VISIBLE_ITEMS_CONFIGS, ProductItemProps } from './ProductListItem';
import { ProductListItemImage } from './ProductListItemImage';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';

type ProductListItemPlaceholderProps = {
    product: TypeListedProductFragment;
    size?: ProductItemProps['size'];
    visibleItemsConfig?: ProductItemProps['visibleItemsConfig'];
} & FunctionComponentProps;

export const ProductListItemPlaceholder: FC<ProductListItemPlaceholderProps> = ({
    product,
    className,
    visibleItemsConfig = PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem,
    size = 'large',
}) => {
    return (
        <li
            className={twMergeCustom(
                'border-background-more bg-background-more group relative flex flex-col gap-2.5 rounded-xl border px-2.5 py-5 text-left transition select-none sm:px-5',
                size === 'small' && 'p-5',
                'hover:border-border-less hover:bg-background',
                className,
            )}
        >
            <ExtendedNextLink
                className="text-text-default hover:text-link-default flex flex-col gap-2.5 no-underline select-none hover:no-underline"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                <div className="font-secondary group-hover:text-link-default line-clamp-3 min-h-[3.75rem] text-sm font-semibold group-hover:underline">
                    {product.fullName}
                </div>

                {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                    <ProductPrice
                        className="min-h-6 sm:min-h-7"
                        isPriceFromVisible={visibleItemsConfig.priceFromWord}
                        productPrice={product.price}
                    />
                )}

                {visibleItemsConfig.storeAvailability && (
                    <ProductAvailability
                        availability={product.availability}
                        availableStoresCount={product.availableStoresCount}
                        className="xs:min-h-[60px] min-h-10 sm:min-h-10"
                        isInquiryType={product.isInquiryType}
                    />
                )}
            </ExtendedNextLink>

            {(visibleItemsConfig.addToCart || visibleItemsConfig.productListButtons) && (
                <div className="flex w-full items-center justify-between gap-1 sm:justify-normal sm:gap-2.5">
                    {visibleItemsConfig.addToCart && <Skeleton className="h-9 w-1/2" />}

                    {visibleItemsConfig.productListButtons && (
                        <>
                            <Skeleton className="size-6" />
                            <Skeleton className="size-6" />
                        </>
                    )}
                </div>
            )}
        </li>
    );
};
