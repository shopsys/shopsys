import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { useEffect, useRef, useState } from 'react';
import { desktopFirstSizes } from 'utils/mediaQueries';
import { twMergeCustom } from 'utils/twMerge';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useEffectOnce } from 'utils/useEffectOnce';

export type BannersDotProps = {
    index: number;
    isActive: boolean;
    sliderItem: TypeSliderItemFragment;
    moveToSlide: (slideToMoveTo: number) => void;
    slideInterval: number;
};

export const BannersDot: FC<BannersDotProps> = ({ index, isActive, sliderItem, moveToSlide, slideInterval }) => {
    const dotRef = useRef<HTMLButtonElement>(null);
    const [start, setStart] = useState(false);
    const { width } = useGetWindowSize();
    const isDesktop = width > desktopFirstSizes.notLargeDesktop;

    useEffectOnce(() => {
        setStart(true);
    });

    useEffect(() => {
        if (isDesktop && isActive && dotRef.current && isWholeElementVisible(dotRef.current)) {
            dotRef.current.scrollIntoView({
                behavior: 'smooth',
                inline: 'end',
                block: 'nearest',
            });
        }
    }, [isActive]);

    return (
        <button
            key={sliderItem.uuid}
            ref={dotRef}
            className={twMergeCustom(
                'group relative block size-4 cursor-pointer rounded-full transition',
                'bg-labelLinkBackground text-text vl:bg-backgroundMore',
                'vl:border-1 text-left vl:flex vl:h-auto vl:w-full vl:rounded-none vl:border-r vl:border-borderAccentLess vl:px-5 vl:py-2',
                isActive && 'bg-textAccent vl:bg-background vl:text-textAccent',
            )}
            onClick={() => moveToSlide(index)}
        >
            <h6 className="hidden vl:inline-block">{sliderItem.name}</h6>
            <div
                className={twMergeCustom(
                    'absolute left-0 top-0 z-above hidden h-[3px] w-0 bg-textAccent transition-all duration-[0s] ease-linear vl:block',
                    isActive && start && `w-full duration-[${slideInterval / 1000}s]`,
                )}
            />
        </button>
    );
};
