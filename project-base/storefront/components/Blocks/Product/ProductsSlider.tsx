import { Icon } from 'components/Basic/Icon/Icon';
import { SliderProductItem } from './SliderProductItem';
import { mediaQueries } from 'components/Theme/mediaQueries';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useGtmSliderProductListViewEvent } from 'hooks/gtm/productList/useGtmSliderProductListViewEvent';
import { useKeenSlider } from 'keen-slider/react';
import { useState } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { twMergeCustom } from 'utils/twMerge';
import { ProductComparePopup } from 'components/Blocks/Product/ButtonsAction/ProductComparePopup';
import { useComparison } from 'hooks/comparison/useComparison';
import { useWishlist } from 'hooks/useWishlist';

type ProductsSliderProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
};

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [areControlsVisible, setAreControlsVisible] = useState<boolean | undefined>(false);
    const [sliderRef, slider] = useKeenSlider<HTMLDivElement>({
        loop: products.length > 4,
        autoAdjustSlidesPerView: false,
        slidesPerView: 4,
        controls: products.length > 4,
        breakpoints: {
            [mediaQueries.queryNotLargeDesktop]: {
                loop: products.length > 3,
                autoAdjustSlidesPerView: false,
                slidesPerView: 3,
                controls: products.length > 3,
            },
            [mediaQueries.queryTablet]: {
                loop: products.length > 2,
                autoAdjustSlidesPerView: false,
                slidesPerView: 2.15,
                controls: products.length > 2,
            },
            [mediaQueries.queryMobile]: {
                loop: products.length > 1,
                autoAdjustSlidesPerView: false,
                slidesPerView: 1.15,
                controls: products.length > 1,
                centered: true,
            },
        },
        slideChanged(slider) {
            setCurrentSlide(slider.details().relativeSlide);
        },
        move(slider) {
            setAreControlsVisible(slider.options().controls);
        },
    });
    useGtmSliderProductListViewEvent(products, gtmProductListName);

    const { isPopupCompareOpen, handleProductInComparison, setIsPopupCompareOpen, isProductInComparison } =
        useComparison();
    const { handleProductInWishlist, isProductInWishlist } = useWishlist();

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
                        gtmProductListName={gtmProductListName}
                        gtmMessageOrigin={gtmMessageOrigin}
                        listIndex={index}
                        isProductInComparison={isProductInComparison(productItemData.uuid)}
                        onProductInComparisonClick={() => handleProductInComparison(productItemData.uuid)}
                        handleProductInWishlist={() => handleProductInWishlist(productItemData.uuid)}
                        isInWishlist={isProductInWishlist(productItemData.uuid)}
                    />
                ))}
            </div>
            {/* eslint-disable-next-line @typescript-eslint/no-unnecessary-condition */}
            {slider && areControlsVisible ? (
                <div className="absolute -top-11 right-0 hidden items-center justify-center lg:flex ">
                    <SliderButton type="prev" onClick={onMoveToPreviousSlideHandler} />
                    <SliderButton type="next" onClick={onMoveToNextSlideHandler} />
                </div>
            ) : null}

            <ProductComparePopup isVisible={isPopupCompareOpen} onCloseCallback={() => setIsPopupCompareOpen(false)} />
        </div>
    );
};

const SliderButton: FC<{ type?: 'prev' | 'next'; onClick: () => void }> = ({ type, onClick }) => (
    <button
        className={twMergeCustom(
            'ml-1 h-8 w-8 cursor-pointer rounded border-none bg-greyDark pt-1 text-creamWhite outline-none transition hover:bg-greyDarker',
        )}
        onClick={onClick}
    >
        <Icon className={twMergeCustom('rotate-90', type === 'next' && '-rotate-90')} iconType="icon" icon="Arrow" />
    </button>
);
