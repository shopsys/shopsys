import { ListedProductFragmentApi } from 'graphql/generated';
import { RefObject, createRef, useEffect, useState } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';
import { ProductsListContent } from './ProductsList/ProductsListContent';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';

type ProductsSliderProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
};

const productTwClass = 'snap-center border-b-0 md:snap-start';

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    dataTestId,
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

            <ProductsListContent
                productRefs={productElementRefs}
                products={products}
                className="grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] [&::-webkit-scrollbar]:hidden"
                gtmProductListName={gtmProductListName}
                gtmMessageOrigin={gtmMessageOrigin}
                classNameProduct={productTwClass}
            />
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
        <ArrowIcon className={twMergeCustom('-translate-y-[2px] rotate-90', type === 'next' && '-rotate-90')} />
    </button>
);
