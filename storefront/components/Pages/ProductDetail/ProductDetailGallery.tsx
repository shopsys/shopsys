import {
    ProductDetailGalleryMainImageStyled,
    ProductDetailGalleryThumbnailsItemStyled,
    ProductDetailGalleryThumbnailsStyled,
} from './ProductDetailGallery.style';
import SimpleReactLightbox, { SRLWrapper } from 'simple-react-lightbox';
import { FC } from 'react';
import ProductDetailImageSlider from './ProductDetailImageSlider';

/**
 * Product detail gallery with simple lightbox and beside thumbnails
 */
const ProductDetailGallery: FC = () => {
    /* TODO PRG: join live data */
    const productDetailSliderItems = [
        {
            type: 'web',
            position: 1,
            size: 'default',
            url: 'http://placeimg.com/640/530/any?t=1',
            width: 968,
            height: 318,
        },
        {
            type: 'web',
            position: 2,
            size: 'default',
            url: 'http://placeimg.com/640/530/any?t=2',
            width: 968,
            height: 318,
        },
    ];

    if (
        productDetailSliderItems === undefined ||
        (Array.isArray(productDetailSliderItems) && productDetailSliderItems.length === 0)
    ) {
        return null;
    }

    return (
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
                    <ProductDetailGalleryThumbnailsItemStyled>
                        <a href="http://placeimg.com/640/530/any?t=1">
                            <img src="http://placeimg.com/64/53/any?t=1" alt="Umbrella" width={64} height={53} />
                        </a>
                    </ProductDetailGalleryThumbnailsItemStyled>

                    <ProductDetailGalleryThumbnailsItemStyled>
                        <a href="http://placeimg.com/640/530/any?t=2">
                            <img src="http://placeimg.com/64/53/any?t=2" alt="Umbrella" width={64} height={53} />
                        </a>
                    </ProductDetailGalleryThumbnailsItemStyled>
                </ProductDetailGalleryThumbnailsStyled>
                <ProductDetailGalleryMainImageStyled>
                    <a href="http://placeimg.com/640/530/any?t=3">
                        <img
                            src="http://placeimg.com/640/530/any?t=3"
                            alt="Picture of the author"
                            width={552}
                            height={454}
                        />
                    </a>
                </ProductDetailGalleryMainImageStyled>
                <ProductDetailImageSlider galleryItems={productDetailSliderItems} />
            </SRLWrapper>
        </SimpleReactLightbox>
    );
};

/* @component */
export default ProductDetailGallery;
