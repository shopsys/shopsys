import { SliderProductItem } from './SliderProductItem';
import { theme } from 'components/Theme/main';
import { useGtmSliderProductListView } from 'hooks/gtm/useGtmSliderProductListView';
import { useKeenSlider } from 'keen-slider/react';
import { useState } from 'react';
import { GtmListNameType } from 'types/gtm';
import { SliderProductItemType } from 'types/product';

type ProductsSliderProps = {
    products: SliderProductItemType[];
    gtmListName: GtmListNameType;
};

export const ProductsSlider: FC<ProductsSliderProps> = ({ products, gtmListName }) => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [areControlsVisible, setAreControlsVisible] = useState<boolean | undefined>(false);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: products.length > 4,
        autoAdjustSlidesPerView: false,
        slidesPerView: 4,
        controls: products.length > 4,
        breakpoints: {
            [theme.mediaQueries.queryNotLargeDesktop]: {
                loop: products.length > 3,
                autoAdjustSlidesPerView: false,
                slidesPerView: 3,
                controls: products.length > 3,
            },
            [theme.mediaQueries.queryTablet]: {
                loop: products.length > 2,
                autoAdjustSlidesPerView: false,
                slidesPerView: 2,
                controls: products.length > 2,
            },
            [theme.mediaQueries.queryMobile]: {
                loop: products.length > 1,
                autoAdjustSlidesPerView: false,
                slidesPerView: 1,
                controls: products.length > 1,
            },
        },
        slideChanged(slider) {
            setCurrentSlide(slider.details().relativeSlide);
        },
        move(slider) {
            setAreControlsVisible(slider.options().controls);
        },
    });
    useGtmSliderProductListView(products, gtmListName);

    const onMoveToNextSlideHandler = () => {
        slider.moveToSlide(currentSlide + 1);
    };

    const onMoveToPreviousSlideHandler = () => {
        slider.moveToSlide(currentSlide - 1);
    };

    return (
        <div className="relative">
            <div ref={sliderRef} className="keen-slider relative -mx-2 flex overflow-hidden">
                {products.map((productItemData, index) => (
                    <SliderProductItem
                        key={productItemData.uuid}
                        product={productItemData}
                        gtmListName={gtmListName}
                        listIndex={index}
                    />
                ))}
            </div>
            {/* eslint-disable-next-line @typescript-eslint/no-unnecessary-condition */}
            {slider !== null && areControlsVisible ? (
                <div className="absolute -top-11 right-0 hidden items-center justify-center lg:flex ">
                    <SliderButton onClick={onMoveToPreviousSlideHandler}>p</SliderButton>
                    <SliderButton onClick={onMoveToNextSlideHandler}>n</SliderButton>
                </div>
            ) : null}
        </div>
    );
};

const SliderButton: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <button
        className="ml-1 h-8 w-8 cursor-pointer rounded border-none bg-greyDark text-creamWhite outline-none transition hover:bg-greyDarker"
        onClick={onClick}
    >
        {children}
    </button>
);
