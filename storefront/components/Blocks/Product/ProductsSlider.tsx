import { ProductSliderControlsStyled, ProductSliderStyled, ProductSliderWrapperStyled } from './ProductsSlider.style';
import { SliderProductItem } from './SliderProductItem';
import { theme } from 'components/Theme/main';
import { useGtmSliderProductListView } from 'hooks/gtm/useGtmSliderProductListView';
import { useKeenSlider } from 'keen-slider/react';
import { FC, useState } from 'react';
import { GtmListNameType } from 'types/gtm';
import { SliderProductItemType } from 'types/product';

type ProductsSliderProps = {
    products: SliderProductItemType[];
    gtmListName: GtmListNameType;
};

export const ProductsSlider: FC<ProductsSliderProps> = (props) => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [areControlsVisible, setAreControlsVisible] = useState<boolean | undefined>(false);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: props.products.length > 4,
        autoAdjustSlidesPerView: false,
        slidesPerView: 4,
        controls: props.products.length > 4,
        breakpoints: {
            [theme.mediaQueries.queryNotLargeDesktop]: {
                loop: props.products.length > 3,
                autoAdjustSlidesPerView: false,
                slidesPerView: 3,
                controls: props.products.length > 3,
            },
            [theme.mediaQueries.queryTablet]: {
                loop: props.products.length > 2,
                autoAdjustSlidesPerView: false,
                slidesPerView: 2,
                controls: props.products.length > 2,
            },
            [theme.mediaQueries.queryMobile]: {
                loop: props.products.length > 1,
                autoAdjustSlidesPerView: false,
                slidesPerView: 1,
                controls: props.products.length > 1,
            },
        },
        slideChanged(slider) {
            setCurrentSlide(slider.details().relativeSlide);
        },
        move(slider) {
            setAreControlsVisible(slider.options().controls);
        },
    });
    useGtmSliderProductListView(props.products, props.gtmListName);

    const onMoveToNextSlideHandler = () => {
        slider.moveToSlide(currentSlide + 1);
    };

    const onMoveToPreviousSlideHandler = () => {
        slider.moveToSlide(currentSlide - 1);
    };

    return (
        <ProductSliderWrapperStyled>
            <ProductSliderStyled ref={sliderRef} className="keen-slider">
                {props.products.map((productItemData, index) => (
                    <SliderProductItem
                        key={productItemData.uuid}
                        product={productItemData}
                        gtmListName={props.gtmListName}
                        listIndex={index}
                    />
                ))}
            </ProductSliderStyled>
            {/* eslint-disable-next-line @typescript-eslint/no-unnecessary-condition */}
            {slider !== null && areControlsVisible ? (
                <ProductSliderControlsStyled>
                    <button onClick={onMoveToPreviousSlideHandler}>p</button>
                    <button onClick={onMoveToNextSlideHandler}>n</button>
                </ProductSliderControlsStyled>
            ) : null}
        </ProductSliderWrapperStyled>
    );
};
