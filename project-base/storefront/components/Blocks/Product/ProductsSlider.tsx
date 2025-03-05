import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductsListContent } from './ProductsList/ProductsListContent';
import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmSliderProductListViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { RefObject, createRef, useEffect, useRef, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { isTextSelected } from 'utils/ui/isTextSelected';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { wait } from 'utils/wait';

export const VISIBLE_SLIDER_ITEMS = 5;
export const VISIBLE_SLIDER_ITEMS_LAST_VISITED = 8;
export const VISIBLE_SLIDER_ITEMS_ARTICLE = 3;
export const VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE = 5;

type ProductsSliderVariant = 'default' | 'article' | 'lastVisited' | 'autocomplete';
export type ProductsSliderProps = {
    products: TypeListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin?: GtmMessageOriginType;
    isWithArrows?: boolean;
    wrapperClassName?: string;
    productItemProps?: Partial<ProductItemProps>;
    visibleSliderItems?: number;
    variant?: ProductsSliderVariant;
    isLuigisEnabled?: boolean;
    ariaAnchorName: string;
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
    isLuigisEnabled,
    ariaAnchorName,
}) => {
    const { t } = useTranslation();
    const sliderRef = useRef<HTMLDivElement>(null);
    const [productElementRefs, setProductElementRefs] = useState<Array<RefObject<HTMLLIElement>>>();
    const [activeIndex, setActiveIndex] = useState(0);
    const isMobile = !useMediaMin('vl');
    const isSmallDesktop = !useMediaMin('xl') && !isMobile;
    const minimumVisibleItemsOnSmallDesktop = 3;
    const currentVisibleItems =
        isSmallDesktop && visibleSliderItems > minimumVisibleItemsOnSmallDesktop
            ? visibleSliderItems - 1
            : visibleSliderItems;
    const isWithControls = products.length > currentVisibleItems && isWithArrows;

    const keyboardFocusableProductIndices = Array.from(
        { length: Math.min(currentVisibleItems, products.length - activeIndex) },
        (_, i) => activeIndex + i,
    );

    useEffect(() => {
        setProductElementRefs(
            Array(products.length)
                .fill(null)
                .map(() => createRef()),
        );
    }, [products.length]);

    useEffect(() => {
        if (!isMobile) {
            handleScroll(activeIndex);
        }
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

        const newActiveIndex = isFirstSlide ? productElementRefs!.length - currentVisibleItems : prevIndex;

        if (!isTextSelected()) {
            setActiveIndex(newActiveIndex);
        }
    };

    const handleNext = () => {
        const nextIndex = activeIndex + 1;
        const isEndSlide = nextIndex > productElementRefs!.length - currentVisibleItems;

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
            case 'article':
                return 'auto-cols-[80%] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[33.33%]';
            case 'lastVisited':
                return 'auto-cols-[140px] sm:auto-cols-[30%] lg:auto-cols-[19.5%] vl:auto-cols-[14.5%] xl:auto-cols-[12.5%]';
            case 'autocomplete':
                return 'auto-cols-[140px] sm:auto-cols-[32%] md:auto-cols-[24%] lg:auto-cols-[20%]';
            default:
                return '';
        }
    };

    useGtmSliderProductListViewEvent(products, gtmProductListName, isLuigisEnabled);

    return (
        <>
            <div className="relative" data-tid={tid}>
                <AccessibleLink className="w-auto" href={`#${ariaAnchorName}`} title={t('Skip product slider')} />

                {isWithControls && (
                    <div className="vl:flex absolute -top-10 right-0 hidden items-center justify-center gap-2">
                        <SliderButton
                            ariaLabel={t('Show previous products in slider')}
                            title={t('Previous products')}
                            type="prev"
                            onClick={handlePrevious}
                        />
                        <SliderButton
                            ariaLabel={t('Show next products in slider')}
                            title={t('Next products')}
                            type="next"
                            onClick={handleNext}
                        />
                    </div>
                )}

                <div ref={sliderRef} tabIndex={-1}>
                    <ProductsListContent
                        gtmMessageOrigin={gtmMessageOrigin}
                        gtmProductListName={gtmProductListName}
                        keyboardFocusableProductIndices={keyboardFocusableProductIndices}
                        productRefs={productElementRefs}
                        products={products}
                        swipeHandlers={handlers}
                        className={twMergeCustom([
                            'hide-scrollbar grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain',
                            productSliderTwClass(variant),
                            wrapperClassName,
                        ])}
                        productItemProps={{
                            className: twMergeCustom(
                                'snap-center md:snap-start mx-1 md:mx-2 first:ml-0 last:mr-0',
                                productItemProps?.className,
                            ),
                            ...productItemProps,
                        }}
                    />
                </div>
            </div>

            <div className="sr-only" id={ariaAnchorName} />
        </>
    );
};

type SliderButtonProps = {
    type?: 'prev' | 'next';
    onClick: () => void;
    isDisabled?: boolean;
    title: string;
    ariaLabel: string;
};

const SliderButton: FC<SliderButtonProps> = ({ type, isDisabled, onClick, title, ariaLabel }) => (
    <button
        aria-label={ariaLabel}
        className="text-icon hover:text-icon-accent disabled:text-text-disabled cursor-pointer rounded-sm border-none p-1 outline-hidden transition disabled:cursor-auto"
        disabled={isDisabled}
        tabIndex={0}
        title={title}
        onClick={onClick}
    >
        <ArrowSecondaryIcon className={twJoin('w-5', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </button>
);
