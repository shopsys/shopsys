import {
    ProductDetailGalleryFlagsStyled,
    ProductDetailGalleryMainImageStyled,
    ProductDetailGalleryThumbnailsItemStyled,
    ProductDetailGalleryThumbnailsStyled,
} from './ProductDetailGallery.style';
import { ProductDetailImageSlider } from './ProductDetailImageSlider';
import clsx from 'clsx';
import { Image } from 'components/Basic/Image/Image';
import { ProductFlags } from 'components/Blocks/Product/Flags/ProductFlags';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import LightGallery from 'lightgallery/react';
import { FC, useState } from 'react';
import { SimpleFlagType } from 'types/flag';
import { ImageType } from 'types/image';

type ProductDetailGalleryProps = {
    images: ImageType[];
    productName: string;
    flags: SimpleFlagType[];
};

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName }) => {
    const [isSliderVisible, setSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisibility(false),
        () => setSliderVisibility(true),
        () => setSliderVisibility(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (images.length === 0) {
        return null;
    }

    const mainImage = images[0];
    const mainImageUrl = images[0].sizes?.find((size) => size.size === 'default')?.url;

    return isSliderVisible ? (
        <ProductDetailImageSlider galleryItems={images} flags={flags} />
    ) : (
        <LightGallery mode="lg-fade" thumbnail plugins={[lgThumbnail]} selector=".lightboxItem">
            <ProductDetailGalleryMainImageStyled data-src={mainImageUrl} className="lightboxItem">
                <Image image={mainImage} alt={productName} type="default" maxHeight="400px" />
                <ProductDetailGalleryFlagsStyled>
                    <ProductFlags flags={flags} />
                </ProductDetailGalleryFlagsStyled>
            </ProductDetailGalleryMainImageStyled>
            <ProductDetailGalleryThumbnailsStyled>
                {images.map(
                    (image, index) =>
                        index > 0 && (
                            <ProductDetailGalleryThumbnailsItemStyled
                                key={index}
                                className={clsx('lightboxItem', index > 6 && 'isHidden')}
                                data-src={image.sizes?.find((size) => size.size === 'default')?.url}
                            >
                                <Image image={image} alt={productName} type="default" />
                            </ProductDetailGalleryThumbnailsItemStyled>
                        ),
                )}
            </ProductDetailGalleryThumbnailsStyled>
        </LightGallery>
    );
};
