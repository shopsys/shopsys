import 'keen-slider/keen-slider.min.css';
import { FC, useEffect, useState } from 'react';
import {
    ImageSliderControlNextStyled,
    ImageSliderControlPreviousStyled,
    ProductDetailImageSliderBoxStyled,
    ProductDetailImageSliderItemStyled,
    ProductDetailImageSliderStyled,
    SliderItemImageStyled,
} from './ProductDetailImageSlider.style';
import { ProductDetailImageType } from './types';
import { theme } from 'components/Theme/main';
import { useKeenSlider } from 'keen-slider/react';

type ProductDetailImageSliderProps = {
    galleryItems: ProductDetailImageType[];
};

const ProductDetailImageSlider: FC<ProductDetailImageSliderProps> = (props) => {
    const [areControlsVisible, setAreControlsVisible] = useState<boolean | undefined>(false);
    const [loadedImageUrls, setLoadedImageUrls] = useState<{ [key: string]: boolean }>({});
    const [currentSlide, setCurrentSlide] = useState(0);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: false,
        duration: 1000,
        breakpoints: {
            [theme.mediaQueries.queryTablet]: {
                slidesPerView: 1,
                spacing: 0,
            },
        },
        slideChanged(slider) {
            setCurrentSlide(slider.details().relativeSlide);
        },
        created(slider) {
            setLoadedImageUrls((currentLoadedImageUrls) => {
                const newLoadedImageUrls = { ...currentLoadedImageUrls };
                const slidesPerView = slider.options().slidesPerView;
                if (slidesPerView !== undefined) {
                    for (let i = 0; i < slidesPerView; i++) {
                        newLoadedImageUrls[i] = true;
                    }

                    if (slider.options().centered) {
                        newLoadedImageUrls[props.galleryItems.length - 1] = true;
                    }
                }
                return newLoadedImageUrls;
            });
        },
        move(slider) {
            setAreControlsVisible(slider.options().controls);
        },
    });
    useEffect(() => {
        setLoadedImageUrls((currentLoadedImageUrls) => {
            const newLoadedImageUrls = { ...currentLoadedImageUrls };
            newLoadedImageUrls[currentSlide] = true;
            if (slider !== null && slider.options().centered) {
                newLoadedImageUrls[Math.min(currentSlide + 1, props.galleryItems.length - 1)] = true;
            }
            return newLoadedImageUrls;
        });
    }, [currentSlide]);

    const onMoveToNextSlideHandler = () => {
        slider.moveToSlide(currentSlide + 1);
    };

    const onMoveToPreviousSlideHandler = () => {
        slider.moveToSlide(currentSlide - 1);
    };

    return (
        <ProductDetailImageSliderBoxStyled>
            <ProductDetailImageSliderStyled ref={sliderRef} className="keen-slider">
                {props.galleryItems.map((galleryItem, index) => (
                    <ProductDetailImageSliderItemStyled key={index} className="keen-slider__slide">
                        <SliderItemImageStyled src={loadedImageUrls[index] ? galleryItem.default?.url : ''} />
                    </ProductDetailImageSliderItemStyled>
                ))}
            </ProductDetailImageSliderStyled>
            {slider !== null && areControlsVisible ? (
                <>
                    <ImageSliderControlPreviousStyled onClick={onMoveToPreviousSlideHandler}>
                        p
                    </ImageSliderControlPreviousStyled>
                    <ImageSliderControlNextStyled onClick={onMoveToNextSlideHandler}>n</ImageSliderControlNextStyled>
                </>
            ) : null}
        </ProductDetailImageSliderBoxStyled>
    );
};

export default ProductDetailImageSlider;
