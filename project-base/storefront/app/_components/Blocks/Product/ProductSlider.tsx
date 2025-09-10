'use client';

import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { useTranslation } from 'components/providers/TranslationProvider';
import { useEffect, useRef, useState } from 'react';
import { useSwipeable } from 'react-swipeable';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { isTextSelected } from 'utils/ui/isTextSelected';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { wait } from 'utils/wait';

export const getVisibleItemsByVariant = (variant: ProductsSliderVariant): number => {
    switch (variant) {
        case 'default':
            return 5;
        case 'lastVisited':
            return 8;
        case 'blog':
            return 3;
        case 'article':
            return 4;
        case 'autocomplete':
            return 5;
        default:
            return 5;
    }
};

type ProductsSliderVariant = 'default' | 'blog' | 'article' | 'lastVisited' | 'autocomplete';

type ProductSliderProps = {
    children: React.ReactNode;
    totalItems: number;
    // products: TypeListedProductFragment[];
    // gtmProductListName: GtmProductListNameType;
    // gtmMessageOrigin?: GtmMessageOriginType;
    isWithArrows?: boolean;
    variant: ProductsSliderVariant;
    isLuigisEnabled?: boolean;
    ariaAnchorName: string;
};

export const ProductSlider: FC<ProductSliderProps> = ({
    children,
    totalItems,
    // gtmProductListName,
    // gtmMessageOrigin = GtmMessageOriginType.other,
    tid,
    isWithArrows = true,
    variant = 'default',
    //isLuigisEnabled, // TODO: luigis box
    ariaAnchorName,
}) => {
    const { t } = useTranslation();
    const sliderRef = useRef<HTMLDivElement>(null);
    const [activeIndex, setActiveIndex] = useState(0);
    const isMobile = !useMediaMin('vl');

    const isSmallDesktop = !useMediaMin('xl') && !isMobile;
    const minimumVisibleItemsOnSmallDesktop = 3;

    const visibleSliderItems = getVisibleItemsByVariant(variant);
    const currentVisibleItems =
        isSmallDesktop && visibleSliderItems > minimumVisibleItemsOnSmallDesktop
            ? visibleSliderItems - 1
            : visibleSliderItems;
    const isWithControls = totalItems > currentVisibleItems && isWithArrows;

    useEffect(() => {
        if (!isMobile) {
            handleScroll(activeIndex);
        }
    }, [activeIndex]);

    const handleScroll = async (selectedActiveIndex: number) => {
        if (!sliderRef.current) {
            return;
        }

        const items = Array.from(sliderRef.current.children[0].children) as HTMLLIElement[];
        const selectedElement = items[selectedActiveIndex];

        if (!isWholeElementVisible(selectedElement)) {
            sliderRef.current.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
            await wait(350);
        }

        selectedElement.scrollIntoView({
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

        const newActiveIndex = isFirstSlide ? totalItems - currentVisibleItems : prevIndex;

        if (!isTextSelected()) {
            setActiveIndex(newActiveIndex);
        }
    };

    const handleNext = () => {
        const nextIndex = activeIndex + 1;
        const isEndSlide = nextIndex > totalItems - currentVisibleItems;

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
                return 'auto-cols-[148px] md:auto-cols-[156px]';
            default:
                return '';
        }
    };

    // useGtmSliderProductListViewEvent(products, gtmProductListName, isLuigisEnabled);

    return (
        <>
            <div className="relative" tid={tid}>
                <AccessibleLink className="w-auto" href={`#${ariaAnchorName}`} title={t('Skip product slider')} />

                {isWithControls && (
                    <div className="vl:flex absolute -top-10 right-0 hidden items-center justify-center gap-2">
                        <SliderButton title={t('Previous products')} type="prev" onClick={handlePrevious} />
                        <SliderButton title={t('Next products')} type="next" onClick={handleNext} />
                    </div>
                )}

                <div ref={sliderRef}>
                    <ul
                        className={twMergeCustom([
                            "grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden",
                            productSliderTwClass(variant),
                        ])}
                        {...handlers}
                    >
                        {children}
                    </ul>
                </div>
            </div>

            <div className="sr-only" id={ariaAnchorName} />
        </>
    );
};

type SliderButtonProps = { type?: 'prev' | 'next'; onClick: () => void; isDisabled?: boolean; title: string };

const SliderButton: FC<SliderButtonProps> = ({ type, isDisabled, onClick, title }) => (
    <button
        className="text-text-default hover:text-text-accent disabled:text-text-disabled cursor-pointer rounded-sm border-none p-1 outline-hidden transition disabled:cursor-auto"
        disabled={isDisabled}
        title={title}
        onClick={onClick}
    >
        <ArrowSecondaryIcon className={twJoin('w-5', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </button>
);
