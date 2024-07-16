import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductsListContent } from './ProductsList/ProductsListContent';
import { ArrowRightIcon } from 'components/Basic/Icon/ArrowRightIcon';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useRef, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { wait } from 'utils/wait';

export type ProductsSliderProps = {
    products: TypeListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
    isWithArrows?: boolean;
    wrapperClassName?: string;
    productItemProps?: Partial<ProductItemProps>;
};

const productTwClass = 'snap-center border-b-0 md:snap-start mx-1.5 first:ml-0 last:mr-0';

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    tid,
    wrapperClassName,
    isWithArrows = true,
    productItemProps,
}) => {
    const { t } = useTranslation();
    const maxVisibleSlides = 4;
    const sliderRef = useRef<HTMLDivElement>(null);
    const [productElementRefs, setProductElementRefs] = useState<Array<RefObject<HTMLLIElement>>>();
    const [activeIndex, setActiveIndex] = useState(0);
    const isWithControls = products.length > maxVisibleSlides && isWithArrows;
    const isMobile = !useMediaMin('vl');

    useEffect(() => {
        setProductElementRefs(
            Array(products.length)
                .fill(null)
                .map(() => createRef()),
        );
    }, [products.length]);

    useEffect(() => {
        handleScroll(activeIndex);
    }, [activeIndex]);

    const handleScroll = async (selectedActiveIndex: number) => {
        const selectedElement = productElementRefs?.[selectedActiveIndex]?.current;

        if (selectedElement && !isWholeElementVisible(selectedElement)) {
            sliderRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
            await wait(350);
        }

        selectedElement?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'start',
        });
    };

    const handlePrevious = () => {
        const prevIndex = activeIndex - 1;
        const isFirstSlide = activeIndex === 0;

        if (isMobile && isFirstSlide) {
            return;
        }

        const newActiveIndex = isFirstSlide ? productElementRefs!.length - 4 : prevIndex;

        setActiveIndex(newActiveIndex);
    };

    const handleNext = () => {
        const nextIndex = activeIndex + 1;
        const isEndSlide = nextIndex + maxVisibleSlides > productElementRefs!.length;

        if (isMobile && isEndSlide) {
            return;
        }

        const newActiveIndex = isEndSlide ? 0 : nextIndex;

        setActiveIndex(newActiveIndex);
    };

    const handlers = useSwipeable({
        onSwipedLeft: handleNext,
        onSwipedRight: handlePrevious,
        trackMouse: true,
    });

    return (
        <div className="relative" tid={tid}>
            {isWithControls && (
                <div className="absolute -top-10 right-0 hidden items-center justify-center vl:flex gap-2">
                    <SliderButton title={t('Previous products')} type="prev" onClick={handlePrevious} />
                    <SliderButton title={t('Next products')} type="next" onClick={handleNext} />
                </div>
            )}

            <div ref={sliderRef}>
                <ProductsListContent
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    productRefs={productElementRefs}
                    products={products}
                    swipeHandlers={handlers}
                    className={twMergeCustom([
                        "grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] [&::-webkit-scrollbar]:hidden vl:auto-cols-[25%]",
                        wrapperClassName,
                    ])}
                    productItemProps={{
                        className: twMergeCustom(productTwClass, productItemProps?.className),
                        ...productItemProps,
                    }}
                />
            </div>
        </div>
    );
};

type SliderButtonProps = { type?: 'prev' | 'next'; onClick: () => void; isDisabled?: boolean; title: string };

const SliderButton: FC<SliderButtonProps> = ({ type, isDisabled, onClick, title }) => (
    <button
        className="cursor-pointer disabled:cursor-auto rounded border-none p-1 outline-none transition text-dark hover:text-primary disabled:text-graySlate"
        disabled={isDisabled}
        title={title}
        onClick={onClick}
    >
        <ArrowRightIcon className={twJoin('w-5', type === 'prev' && 'rotate-180')} />
    </button>
);
