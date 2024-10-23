import { PREDEFINED_VISIBLE_ITEMS_CONFIGS, ProductItemProps } from './ProductListItem';
import { ProductListItemImage } from './ProductListItemImage';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import Skeleton from 'react-loading-skeleton';
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
                'group relative flex select-none flex-col justify-between gap-2.5 rounded-xl border border-backgroundMore bg-backgroundMore px-2.5 py-5 text-left transition sm:px-5',
                'hover:border-borderAccentLess hover:bg-background',
                className,
            )}
        >
            <ExtendedNextLink
                className="flex h-full select-none flex-col justify-between gap-2.5 text-text no-underline hover:text-link hover:no-underline"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <ProductListItemImage product={product} size={size} visibleItemsConfig={{ flags: false }} />

                <div className="line-clamp-3 min-h-[3.75rem] font-secondary text-sm font-semibold group-hover:text-link group-hover:underline">
                    {product.fullName}
                </div>

                <div>
                    {visibleItemsConfig.price && <ProductPrice productPrice={product.price} />}

                    {visibleItemsConfig.storeAvailability && !product.isInquiryType && (
                        <div className="min-h-10">
                            <ProductAvailableStoresCount
                                availableStoresCount={product.availableStoresCount}
                                isMainVariant={product.isMainVariant}
                                name={product.availability.name}
                            />
                        </div>
                    )}
                </div>
            </ExtendedNextLink>

            <div className="flex w-full items-center justify-between gap-1 sm:justify-normal sm:gap-2.5">
                {visibleItemsConfig.addToCart && <Skeleton className="h-10" containerClassName="w-1/2" />}

                {visibleItemsConfig.productListButtons && (
                    <>
                        <Skeleton className="h-8 w-8" />
                        <Skeleton className="h-8 w-8" />
                    </>
                )}
            </div>
        </li>
    );
};
