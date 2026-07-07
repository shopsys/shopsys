import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductActionSkeleton } from 'components/Blocks/Skeleton/ProductActionSkeleton';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';
import { PREDEFINED_VISIBLE_ITEMS_CONFIGS, ProductItemProps } from './ProductListItem';
import { ProductListItemImage } from './ProductListItemImage';

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
                'group relative flex select-none flex-col gap-2.5 rounded-xl border border-background-more bg-background-more px-2.5 py-5 text-left transition sm:px-5',
                size === 'small' && 'p-5',
                'hover:border-border-less hover:bg-background',
                className,
            )}
        >
            <ExtendedNextLink
                className="flex select-none flex-col gap-2.5 text-text-default no-underline hover:text-link-default hover:no-underline"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                <div className="line-clamp-3 min-h-15 font-secondary font-semibold text-sm group-hover:text-link-default group-hover:underline">
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
                        className="min-h-10 xs:min-h-15 sm:min-h-10"
                        isInquiryType={product.isInquiryType}
                    />
                )}
            </ExtendedNextLink>

            {visibleItemsConfig.addToCart && <ProductActionSkeleton isWithAddToCart isWithProductListButtons={false} />}
        </li>
    );
};
