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
                'group relative block size-4 cursor-pointer rounded-full bg-labelLinkBackground transition',
                'vl:flex vl:h-auto vl:w-full vl:rounded-none vl:bg-backgroundMore vl:px-5 vl:py-2 vl:text-left vl:text-text',
                'vl:after:absolute vl:after:inset-0 vl:after:border-b-[1px] vl:after:border-l-[1px] vl:after:border-t-[1px] vl:after:border-borderAccentLess  vl:after:content-[""] vl:after:first-of-type:rounded-bl-md vl:after:last-of-type:rounded-br-md vl:after:last-of-type:border-r-[1px]',
                isActive && 'bg-textAccent vl:bg-background vl:text-textAccent',
            )}
            onClick={() => moveToSlide(index)}
        >
            <h6 className="hidden vl:line-clamp-4">{sliderItem.name}</h6>
            <div
                className={twMergeCustom(
                    'absolute left-0 top-0 z-above hidden h-[3px] w-0 bg-textAccent transition-all duration-[0s] ease-linear vl:block',
                )}
                style={
                    isActive && start ? { transitionDuration: `${slideInterval / 1000}s`, width: '100%' } : undefined
                }
            />
        </button>
    );
};
