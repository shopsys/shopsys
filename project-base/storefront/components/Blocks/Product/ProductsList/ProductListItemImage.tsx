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
    tid?: string;
};

export const ProductListItemImage: FC<ProductListItemImageProps> = ({ product, visibleItemsConfig, size, tid }) => {
    const [imageSizeClassName, imageSize] = (() => {
        switch (size) {
            case 'extraLarge':
                return ['h-[220px] w-[220px]', 220];
            case 'large':
                return ['h-[180px] w-[180px]', 180];
            case 'medium':
                return ['h-[142px] w-[142px]', 142];
            case 'small':
                return ['h-[94px] w-[94px]', 94];
            case 'extraSmall':
                return ['h-[80px] w-[80px]', 80];
            default:
                return ['h-[220px] w-[220px]', 220];
        }
    })();

    return (
        <div className="flex items-center justify-center" tid={tid || TIDs.product_list_item_image}>
            <Image
                alt={product.mainImage?.name || product.fullName}
                className={twJoin('object-contain mix-blend-multiply', imageSizeClassName)}
                draggable={false}
                height={imageSize}
                src={product.mainImage?.url}
                width={imageSize}
            />

            {!!product.flags.length && visibleItemsConfig?.flags && (
                <ProductFlags flags={product.flags} variant="list" />
            )}
        </div>
    );
};
