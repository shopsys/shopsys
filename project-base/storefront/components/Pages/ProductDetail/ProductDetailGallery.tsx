import { ProductDetailGallerySlider } from './ProductDetailGallerySlider';
import { isElementVisible } from 'helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { SimpleFlagFragmentApi } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { ImageSizesFragmentApi } from 'graphql/requests/images/fragments/ImageSizesFragment.generated';
import { VideoTokenFragmentApi } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';

const ProductDetailGalleryImages = dynamic(() =>
    import('./ProductDetailGalleryImages').then((component) => component.ProductDetailGalleryImages),
);

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
