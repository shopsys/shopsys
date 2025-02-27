import { TypeSliderItemFragment } from 'graphql/requests/sliderItems/fragments/SliderItemFragment.generated';
import { useEffect, useRef, useState } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { isWholeElementVisible } from 'utils/ui/isWholeElementVisible';
import { useMediaMin } from 'utils/ui/useMediaMin';

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
    const isDesktop = useMediaMin('vl');

    useEffect(() => setStart(true), []);

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
                'bg-labelLinkBackground group relative block size-4 cursor-pointer rounded-full transition',
                'vl:flex vl:h-auto vl:w-full vl:rounded-none vl:bg-backgroundMore vl:px-5 vl:py-2 vl:text-left vl:text-text',
                'vl:after:absolute vl:after:inset-0 vl:after:border-b-[1px] vl:after:border-l-[1px] vl:after:border-t-[1px] vl:after:border-borderAccentLess vl:after:content-[""] vl:first-of-type:after:rounded-bl-md vl:last-of-type:after:rounded-br-md vl:last-of-type:after:border-r-[1px]',
                isActive && 'bg-textAccent vl:bg-background vl:text-textAccent',
            )}
            onClick={() => moveToSlide(index)}
        >
            <h6 className="vl:line-clamp-4 hidden">{sliderItem.name}</h6>
            <div
                className={twMergeCustom(
                    'z-above bg-textAccent vl:block absolute top-0 left-0 hidden h-[3px] w-0 transition-all duration-[0s] ease-linear',
                )}
                style={
                    isActive && start ? { transitionDuration: `${slideInterval / 1000}s`, width: '100%' } : undefined
                }
            />
        </button>
    );
};
