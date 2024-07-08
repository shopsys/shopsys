import { ProductItemProps } from './ProductListItem';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { twJoin } from 'tailwind-merge';

const ProductFlags = dynamic(() => import('../ProductFlags').then((component) => component.ProductFlags));

type ProductListItemImageProps = {
    size: ProductItemProps['size'];
    product: ProductItemProps['product'];
    visibleItemsConfig: ProductItemProps['visibleItemsConfig'];
};

export const ProductListItemImage: FC<ProductListItemImageProps> = ({ product, visibleItemsConfig, size }) => {
    const [imageSizeClassName, imageSize] = (() => {
        switch (size) {
            case 'extraLarge':
                return ['h-[220px] w-[220px]', 220];
            case 'large':
                return ['h-[180px] w-[180px]', 180];
            case 'medium':
                return ['h-[150px] w-[150px]', 150];
            case 'small':
                return ['h-[94px] w-[94px]', 94];
            default:
                return ['h-[220px] w-[220px]', 220];
        }
    })();

    return (
        <div className="relative flex items-center justify-center mx-auto w-full" tid={TIDs.product_list_item_image}>
            <Image
                alt={product.mainImage?.name || product.fullName}
                className={twJoin('max-h-full object-contain', imageSizeClassName)}
                draggable={false}
                height={imageSize}
                src={product.mainImage?.url}
                width={imageSize}
            />

            {!!product.flags.length && visibleItemsConfig?.flags && (
                <div className="absolute top-0 right-0 flex flex-col items-end justify-end">
                    <ProductFlags flags={product.flags} />
                </div>
            )}
        </div>
    );
};
