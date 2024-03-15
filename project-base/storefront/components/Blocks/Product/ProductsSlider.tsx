import { ProductsListContent } from './ProductsList/ProductsListContent';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { ListedProductFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useState } from 'react';
import { useSwipeable } from 'react-swipeable';

type ProductsSliderProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
};

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    tid,
}) => {
    const { t } = useTranslation();
    const [productElementRefs, setProductElementRefs] = useState<Array<RefObject<HTMLLIElement>>>();
    const [activeIndex, setActiveIndex] = useState(0);
    const isWithControls = products.length > 4;

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
        const isEndSlide = nextIndex + 4 > productElementRefs!.length;
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
                className="scroll-mb-1.5 grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] [&::-webkit-scrollbar]:hidden"
                classNameProduct="snap-center border-b-0 md:snap-start"
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                productRefs={productElementRefs}
                products={products}
                swipeHandlers={handlers}
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
            'ml-1 h-8 w-8 cursor-pointer rounded border-none bg-greyDark pt-1 text-creamWhite outline-none transition hover:bg-greyDarker disabled:bg-greyLighter',
        )}
        onClick={onClick}
    >
        <ArrowIcon className={twMergeCustom('-translate-y-[2px] rotate-90', type === 'next' && '-rotate-90')} />
    </button>
);
