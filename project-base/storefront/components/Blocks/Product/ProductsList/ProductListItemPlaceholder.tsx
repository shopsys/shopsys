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
                'group relative flex select-none flex-col justify-between gap-2.5 p-5 text-left rounded-xl h-ful transition',
                'bg-backgroundMore',
                'hover:bg-backgroundMost',
                className,
            )}
        >
            <ExtendedNextLink
                className="flex h-full select-none flex-col justify-between no-underline hover:no-underline text-text hover:text-text"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <div className="flex flex-col gap-2">
                    <ProductListItemImage product={product} size={size} visibleItemsConfig={{ flags: false }} />

                    <div className="font-semibold font-secondary mb-4">{product.fullName}</div>
                </div>

                <div>
                    {visibleItemsConfig.price && <ProductPrice productPrice={product.price} />}

                    {visibleItemsConfig.storeAvailability && (
                        <div className="flex flex-col gap-1 text-sm text-text">
                            <div>{product.availability.name}</div>
                            <ProductAvailableStoresCount
                                availableStoresCount={product.availableStoresCount}
                                isMainVariant={product.isMainVariant}
                            />
                        </div>
                    )}
                </div>
            </ExtendedNextLink>

            <div className="flex w-full items-center gap-2">
                {visibleItemsConfig.addToCart && <Skeleton className="h-10" containerClassName="w-1/2" />}

                {visibleItemsConfig.productListButtons && (
                    <>
                        <Skeleton className="w-8 h-8" />
                        <Skeleton className="w-8 h-8" />
                    </>
                )}
            </div>
        </li>
    );
};
