import { Icon } from 'components/Basic/Icon/Icon';
import { ListedProductFragmentApi } from 'graphql/generated';
import { RefObject, createRef, useEffect, useRef, useState } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { ProductsListContent } from './ProductsList/ProductsListContent';

type ProductsSliderProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
};

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    dataTestId,
}) => {
    const t = useTypedTranslationFunction();
    const sliderRef = useRef<HTMLDivElement>(null);
    const [productElementRefs, setProductElementRefs] = useState<Array<RefObject<HTMLDivElement>>>();
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

    const handlePreviousClick = () => {
        const prevIndex = activeIndex - 1;
        const newActiveIndex = prevIndex >= 0 ? prevIndex : productElementRefs!.length - 4;

        handleScroll(newActiveIndex);
    };

    const handleNextClick = () => {
        const nextIndex = activeIndex + 1;
        const isEndSlide = nextIndex + 4 > productElementRefs!.length;
        const newActiveIndex = isEndSlide ? 0 : nextIndex;

        handleScroll(newActiveIndex);
    };

    return (
        <div className="relative" data-testid={dataTestId}>
            {isWithControls && (
                <div className="absolute -top-11 right-0 hidden items-center justify-center vl:flex ">
                    <SliderButton type="prev" onClick={handlePreviousClick} title={t('Previous products')} />
                    <SliderButton type="next" onClick={handleNextClick} title={t('Next products')} />
                </div>
            )}

            <div
                className="grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] [&::-webkit-scrollbar]:hidden"
                ref={sliderRef}
            >
                <ProductsListContent
                    productRefs={productElementRefs}
                    products={products}
                    gtmProductListName={gtmProductListName}
                    gtmMessageOrigin={gtmMessageOrigin}
                    className="snap-center border-b-0 md:snap-start"
                />
            </div>
        </div>
    );
};

type SliderButtonProps = { type?: 'prev' | 'next'; onClick: () => void; isDisabled?: boolean; title: string };

const SliderButton: FC<SliderButtonProps> = ({ type, isDisabled, onClick, title }) => (
    <button
        className={twMergeCustom(
            'ml-1 h-8 w-8 cursor-pointer rounded border-none bg-greyDark pt-1 text-creamWhite outline-none transition hover:bg-greyDarker disabled:bg-greyLighter',
        )}
        title={title}
        onClick={onClick}
        disabled={isDisabled}
    >
        <Icon className={twMergeCustom('rotate-90', type === 'next' && '-rotate-90')} iconType="icon" icon="Arrow" />
    </button>
);
