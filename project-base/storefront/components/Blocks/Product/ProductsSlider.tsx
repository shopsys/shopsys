import { ProductsListContent } from './ProductsList/ProductsListContent';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

export type ProductsSliderProps = {
    products: TypeListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
    isWithSimpleCards?: boolean;
};

const productTwClass = 'snap-center border-b-0 md:snap-start';

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    tid,
    isWithSimpleCards,
}) => {
    const { t } = useTranslation();
    const maxVisibleSlides = isWithSimpleCards ? 3 : 4;
    const [productElementRefs, setProductElementRefs] = useState<Array<RefObject<HTMLLIElement>>>();
    const [activeIndex, setActiveIndex] = useState(0);
    const isWithControls = products.length > maxVisibleSlides;

    useEffect(() => {
        setProductElementRefs(
            Array(products.length)
                .fill(null)
                .map(() => createRef()),
        );
    }, []);

    useEffect(() => {
        productElementRefs?.[activeIndex].current?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'start',
        });
    }, [activeIndex]);

    const handleScroll = (productIndex: number) => setActiveIndex(productIndex);

    const handlePrevious = () => {
        const prevIndex = activeIndex - 1;
        const newActiveIndex = prevIndex >= 0 ? prevIndex : productElementRefs!.length - 4;

        handleScroll(newActiveIndex);
    };

    const handleNext = () => {
        const nextIndex = activeIndex + 1;
        const isEndSlide = nextIndex + maxVisibleSlides > productElementRefs!.length;
        const newActiveIndex = isEndSlide ? 0 : nextIndex;

        handleScroll(newActiveIndex);
    };

    const handlers = useSwipeable({
        onSwipedLeft: handleNext,
        onSwipedRight: handlePrevious,
        trackMouse: true,
    });

    return (
        <div className="relative" tid={tid}>
            {isWithControls && (
                <div className="absolute -top-11 right-0 hidden items-center justify-center vl:flex ">
                    <SliderButton title={t('Previous products')} type="prev" onClick={handlePrevious} />
                    <SliderButton title={t('Next products')} type="next" onClick={handleNext} />
                </div>
            )}

            <ProductsListContent
                classNameProduct={productTwClass}
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                isWithSimpleCards={isWithSimpleCards}
                productRefs={productElementRefs}
                products={products}
                swipeHandlers={handlers}
                className={twJoin([
                    "grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] [&::-webkit-scrollbar]:hidden",
                    !isWithSimpleCards && 'vl:auto-cols-[25%]',
                ])}
            />
        </div>
    );
};

type SliderButtonProps = { type?: 'prev' | 'next'; onClick: () => void; isDisabled?: boolean; title: string };

const SliderButton: FC<SliderButtonProps> = ({ type, isDisabled, onClick, title }) => (
    <button
        disabled={isDisabled}
        title={title}
        className={twMergeCustom(
            'ml-1 h-8 w-8 cursor-pointer rounded border-none bg-skyBlue pt-1 text-whiteSnow outline-none transition hover:bg-skyBlue disabled:bg-graySlate',
        )}
        onClick={onClick}
    >
        <ArrowIcon className={twMergeCustom('-translate-y-[2px] rotate-90', type === 'next' && '-rotate-90')} />
    </button>
);
