import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { forwardRef } from 'react';
import { generateProductImageAlt } from 'utils/productAltText';

import { ProductItemProps } from './ProductListItem';
import { ProductListItemGallery, ProductListItemGalleryHandle } from './ProductListItemGallery';

const ProductFlags = dynamic(() =>
    import('components/Blocks/Product/ProductFlags').then((component) => component.ProductFlags),
);

type ProductListItemImageProps = {
    imageCount?: number;
    isWithImageGallery?: boolean;
    size: ProductItemProps['size'];
    product: ProductItemProps['product'];
    visibleItemsConfig: ProductItemProps['visibleItemsConfig'];
    tid?: string;
};

export type ProductListItemImageHandle = ProductListItemGalleryHandle;

export const getProductListItemImageSize = (size: ProductItemProps['size']): number => {
    switch (size) {
        case 'extraLarge':
            return 220;
        case 'large':
            return 180;
        case 'medium':
            return 142;
        case 'small':
            return 94;
        case 'extraSmall':
            return 80;
        default:
            return 220;
    }
};

export const ProductListItemImage = forwardRef<ProductListItemImageHandle, ProductListItemImageProps>(
    ({ product, visibleItemsConfig, size, imageCount = 0, isWithImageGallery = false, tid }, ref) => {
        const imageSize = getProductListItemImageSize(size);
        const imageAlt = generateProductImageAlt(product.fullName, product.categories[0]?.name);
        const isGalleryEnabled = isWithImageGallery && imageCount > 1;

        return (
            <div
                className="mx-auto flex w-full flex-col items-center justify-center"
                data-tid={tid || TIDs.product_list_item_image}
                style={{ maxWidth: imageSize }}
            >
                {isGalleryEnabled ? (
                    <ProductListItemGallery
                        imageAlt={imageAlt}
                        imageCount={imageCount}
                        imageSize={imageSize}
                        product={product}
                        ref={ref}
                    />
                ) : (
                    <>
                        <div className="flex w-full items-center justify-center" style={{ height: imageSize }}>
                            <Image
                                alt={imageAlt}
                                className="h-full w-full object-contain mix-blend-multiply"
                                draggable={false}
                                height={imageSize}
                                src={product.mainImage?.url}
                                width={imageSize}
                            />
                        </div>

                        {isWithImageGallery && <span aria-hidden="true" className="h-4" />}
                    </>
                )}

                <ProductFlags
                    flags={product.flags}
                    percentageDiscount={product.price.percentageDiscount}
                    variant="grid"
                    visibleItemsConfig={visibleItemsConfig}
                />
            </div>
        );
    },
);

ProductListItemImage.displayName = 'ProductListItemImage';
