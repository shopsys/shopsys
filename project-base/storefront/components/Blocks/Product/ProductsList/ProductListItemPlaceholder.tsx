import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import Skeleton from 'react-loading-skeleton';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';

type ProductItemProps = {
    product: TypeListedProductFragment;
} & FunctionComponentProps;

export const ProductListItemPlaceholder: FC<ProductItemProps> = ({ product, className }) => {
    return (
        <li
            className={twMergeCustom(
                'relative flex select-none flex-col justify-between gap-3 border-b border-greyLighter p-3 text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl',
                className,
            )}
        >
            <ExtendedNextLink
                className="flex h-full select-none flex-col gap-3 no-underline hover:no-underline"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <div className="relative flex h-56 items-center justify-center">
                    <Image
                        alt={product.mainImage?.name || product.fullName}
                        className="max-h-full object-contain"
                        draggable={false}
                        height={250}
                        src={product.mainImage?.url}
                        width={250}
                    />
                </div>

                <div className="h-10 overflow-hidden text-lg font-bold leading-5 text-dark">{product.fullName}</div>

                <ProductPrice productPrice={product.price} />

                <div className="flex flex-col gap-1 text-sm text-black">
                    <div>{product.availability.name}</div>
                    <ProductAvailableStoresCount
                        availableStoresCount={product.availableStoresCount}
                        isMainVariant={product.isMainVariant}
                    />
                </div>
            </ExtendedNextLink>

            <div className="flex justify-end gap-2">
                <Skeleton className="w-8 h-8" />
                <Skeleton className="w-8 h-8" />
            </div>

            <Skeleton className="h-12" />
        </li>
    );
};
