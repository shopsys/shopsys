import { useLayoutEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { clamp } from 'utils/numbers/clamp';

type MediaCarouselPaginationProps = {
    itemCount: number;
    selectedIndex: number;
};

type DotSize = 'hidden' | 'small' | 'medium' | 'regular';

const REGULAR_DOT_COUNT = 3;
const DOT_TRACK_WIDTH_PX = 72;

const normalizeRegularDotStartIndex = (index: number, itemCount: number) =>
    clamp(index, 0, Math.max(itemCount - REGULAR_DOT_COUNT, 0));

const getDotSize = (index: number, regularDotStartIndex: number): DotSize => {
    const distanceFromRegularDots = Math.max(
        regularDotStartIndex - index,
        index - (regularDotStartIndex + REGULAR_DOT_COUNT - 1),
        0,
    );

    if (distanceFromRegularDots === 0) {
        return 'regular';
    }

    if (distanceFromRegularDots === 1) {
        return 'medium';
    }

    if (distanceFromRegularDots === 2) {
        return 'small';
    }

    return 'hidden';
};

export const MediaCarouselPagination: FC<MediaCarouselPaginationProps> = ({ itemCount, selectedIndex }) => {
    const [regularDotStartIndex, setRegularDotStartIndex] = useState(() =>
        normalizeRegularDotStartIndex(selectedIndex - 1, itemCount),
    );

    useLayoutEffect(() => {
        setRegularDotStartIndex((currentRegularDotStartIndex) => {
            const normalizedCurrentStartIndex = normalizeRegularDotStartIndex(currentRegularDotStartIndex, itemCount);

            if (selectedIndex < normalizedCurrentStartIndex) {
                return normalizeRegularDotStartIndex(selectedIndex, itemCount);
            }

            if (selectedIndex >= normalizedCurrentStartIndex + REGULAR_DOT_COUNT) {
                return normalizeRegularDotStartIndex(selectedIndex - REGULAR_DOT_COUNT + 1, itemCount);
            }

            return normalizedCurrentStartIndex;
        });
    }, [itemCount, selectedIndex]);

    const dotIndexes = Array.from({ length: itemCount }, (_, index) => index);

    return (
        <div aria-hidden="true" className="h-2 overflow-hidden" style={{ width: DOT_TRACK_WIDTH_PX }}>
            <div className="flex h-2 items-center justify-center">
                {dotIndexes.map((index) => {
                    const dotSize = getDotSize(index, regularDotStartIndex);

                    return (
                        <span
                            key={index}
                            className={twJoin(
                                'flex h-2 shrink-0 items-center justify-center transition-all duration-200 ease-out motion-reduce:transition-none',
                                dotSize === 'regular' && 'w-3',
                                dotSize === 'medium' && 'w-2.5',
                                dotSize === 'small' && 'w-2',
                                dotSize === 'hidden' && 'w-0',
                            )}
                        >
                            <span
                                className={twJoin(
                                    'rounded-full transition-all duration-200 ease-out motion-reduce:transition-none',
                                    index === selectedIndex ? 'bg-background-accent' : 'bg-icon-less/40',
                                    dotSize === 'regular' && 'size-2',
                                    dotSize === 'medium' && 'size-1.5',
                                    dotSize === 'small' && 'size-1',
                                    dotSize === 'hidden' && 'size-0',
                                )}
                            />
                        </span>
                    );
                })}
            </div>
        </div>
    );
};
