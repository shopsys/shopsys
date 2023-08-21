import { isElementVisible } from 'helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ImageSizesFragmentApi, SimpleFlagFragmentApi, VideoTokenFragmentApi } from 'graphql/generated';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useState } from 'react';
import { ProductDetailGalleryImages } from './ProductDetailGalleryImages';
import { ProductDetailGallerySlider } from './ProductDetailGallerySlider';

type ProductDetailGalleryProps = {
    images: ImageSizesFragmentApi[];
    productName: string;
    flags: SimpleFlagFragmentApi[];
    videoIds?: VideoTokenFragmentApi[];
};

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({ flags, images, productName, videoIds }) => {
    const [isSliderVisible, setSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisibility(false),
        () => setSliderVisibility(true),
        () => setSliderVisibility(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (!images.length && !videoIds) {
        return null;
    }

    return isSliderVisible ? (
        <ProductDetailGallerySlider images={images} flags={flags} videoIds={videoIds} />
    ) : (
        <ProductDetailGalleryImages productName={productName} images={images} flags={flags} videoIds={videoIds} />
    );
};