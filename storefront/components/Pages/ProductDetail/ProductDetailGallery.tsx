import { FC, useState } from 'react';
import {
    ProductDetailGalleryMainImageStyled,
    ProductDetailGalleryThumbnailsItemStyled,
    ProductDetailGalleryThumbnailsStyled,
} from './ProductDetailGallery.style';
import SimpleReactLightbox, { SRLWrapper } from 'simple-react-lightbox';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import ProductDetailImageSlider from './ProductDetailImageSlider';
import { ProductDetailImageType } from 'types/product';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';

type ProductDetailGalleryProps = {
    images: ProductDetailImageType[];
    productName: string;
};

/**
 * Product detail gallery with simple lightbox and beside thumbnails
 */
const ProductDetailGallery: FC<ProductDetailGalleryProps> = (props) => {
    const [isSliderVisible, setSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisibility(false),
        () => setSliderVisibility(true),
        () => setSliderVisibility(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    if (props.images.length === 0) {
        return null;
    }

    const mainImage = props.images[0].default;

    return isSliderVisible ? (
        <ProductDetailImageSlider galleryItems={props.images} />
    ) : (
        <SimpleReactLightbox>
            <SRLWrapper
                options={{
                    settings: {
                        overlayColor: 'rgba(11,11,11,0.65)',
                    },
                    buttons: {
                        showDownloadButton: false,
                        showAutoplayButton: false,
                        showThumbnailsButton: false,
                    },
                    thumbnails: {
                        showThumbnails: false,
                    },
                }}
            >
                <ProductDetailGalleryThumbnailsStyled>
                    {props.images.map(
                        (image, index) =>
                            index > 0 && (
                                <ProductDetailGalleryThumbnailsItemStyled key={index}>
                                    <a href={image.default.url}>
                                        <img
                                            src={image.galleryThumbnail.url}
                                            alt={props.productName}
                                            width={image.galleryThumbnail.width}
                                            height={image.galleryThumbnail.height}
                                        />
                                    </a>
                                </ProductDetailGalleryThumbnailsItemStyled>
                            ),
                    )}
                </ProductDetailGalleryThumbnailsStyled>
                <ProductDetailGalleryMainImageStyled>
                    <a href={mainImage.url}>
                        <img
                            src={mainImage.url}
                            alt={props.productName}
                            width={mainImage.width}
                            height={mainImage.height}
                        />
                    </a>
                </ProductDetailGalleryMainImageStyled>
            </SRLWrapper>
        </SimpleReactLightbox>
    );
};

/* @component */
export default ProductDetailGallery;
