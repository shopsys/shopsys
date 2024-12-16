import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductsListContent } from './ProductsList/ProductsListContent';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useRef, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { isTextSelected } from 'utils/ui/disableClickWhenTextSelected';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { wait } from 'utils/wait';

export const VISIBLE_SLIDER_ITEMS = 5;
export const VISIBLE_SLIDER_ITEMS_LAST_VISITED = 8;
export const VISIBLE_SLIDER_ITEMS_BLOG = 3;
export const VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE = 5;

type ProductsSliderVariant = 'default' | 'blog' | 'lastVisited' | 'autocomplete';
export type ProductsSliderProps = {
    products: TypeListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
    isWithArrows?: boolean;
    wrapperClassName?: string;
    productItemProps?: Partial<ProductItemProps>;
    visibleSliderItems?: number;
    variant?: ProductsSliderVariant;
};

export const ProductsSlider: FC<ProductsSliderProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    tid,
    wrapperClassName,
    isWithArrows = true,
    productItemProps,
    visibleSliderItems = VISIBLE_SLIDER_ITEMS,
    variant = 'default',
}) => {
    const { t } = useTranslation();
    const sliderRef = useRef<HTMLDivElement>(null);
    const [productElementRefs, setProductElementRefs] = useState<Array<RefObject<HTMLLIElement>>>();
    const [activeIndex, setActiveIndex] = useState(0);
    const isWithControls = products.length > visibleSliderItems && isWithArrows;
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

        if (!isTextSelected()) {
            setActiveIndex(newActiveIndex);
        }
    };

    const handleNext = () => {
        const nextIndex = activeIndex + 1;
        const isEndSlide = nextIndex + visibleSliderItems > productElementRefs!.length;

        if (isMobile && isEndSlide) {
            return;
        }

        const newActiveIndex = isEndSlide ? 0 : nextIndex;

        if (!isTextSelected()) {
            setActiveIndex(newActiveIndex);
        }
    };

    const handlers = useSwipeable({
        onSwipedLeft: handleNext,
        onSwipedRight: handlePrevious,
        trackMouse: true,
    });

    const productSliderTwClass = (variant: ProductsSliderVariant) => {
        switch (variant) {
            case 'default':
                return 'auto-cols-[225px] sm:auto-cols-[60%]  md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] xl:auto-cols-[20%]';
            case 'blog':
                return 'auto-cols-[80%] lg:auto-cols-[45%] xl:auto-cols-[33.33%]';
            case 'lastVisited':
                return 'auto-cols-[140px] sm:auto-cols-[30%] lg:auto-cols-[19.5%] vl:auto-cols-[14.5%] xl:auto-cols-[12.5%]';
            case 'autocomplete':
                return 'auto-cols-[140px] sm:auto-cols-[32%] md:auto-cols-[24%] lg:auto-cols-[20%]';
            default:
                return '';
        }
    };

    return (
        <div className="relative" tid={tid}>
            {isWithControls && (
                <div
                    className={twMergeCustom(
                        'absolute -top-10 right-0 hidden items-center justify-center gap-2',
                        visibleSliderItems === VISIBLE_SLIDER_ITEMS_BLOG ? 'xl:flex' : 'vl:flex',
                    )}
                >
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
                        "grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden",
                        productSliderTwClass(variant),
                        wrapperClassName,
                    ])}
                    productItemProps={{
                        className: twMergeCustom(
                            'snap-center md:snap-start mx-1 md:mx-2 first:ml-0 last:mr-0 p-0',
                            productItemProps?.className,
                        ),
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
        className="cursor-pointer rounded border-none p-1 text-text outline-none transition hover:text-textAccent disabled:cursor-auto disabled:text-textDisabled"
        disabled={isDisabled}
        title={title}
        onClick={onClick}
    >
        <ArrowSecondaryIcon className={twJoin('w-5', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </button>
);
