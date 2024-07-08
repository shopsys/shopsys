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
    size = 'extraLarge',
}) => {
    return (
        <li
            className={twMergeCustom(
                'group relative flex select-none flex-col justify-between gap-2.5 p-5 text-left rounded-xl h-full bg-grayLight hover:bg-gray transition',
                className,
            )}
        >
            <ExtendedNextLink
                className="flex h-full select-none flex-col gap-3 no-underline hover:no-underline"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <ProductListItemImage product={product} size={size} visibleItemsConfig={{ flags: false }} />

                <div className="h-10 overflow-hidden text-lg font-bold leading-5 text-dark">{product.fullName}</div>

                <ProductPrice productPrice={product.price} />

                {visibleItemsConfig.storeAvailability && (
                    <div className="flex flex-col gap-1 text-sm text-black">
                        <div>{product.availability.name}</div>
                        <ProductAvailableStoresCount
                            availableStoresCount={product.availableStoresCount}
                            isMainVariant={product.isMainVariant}
                        />
                    </div>
                )}
            </ExtendedNextLink>

            {visibleItemsConfig.productListButtons && (
                <div className="flex justify-end gap-2">
                    <Skeleton className="w-8 h-8" />
                    <Skeleton className="w-8 h-8" />
                </div>
            )}

            <Skeleton className="h-12" />
        </li>
    );
};
