import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import { forwardRef, useCallback, useEffect, useImperativeHandle, useLayoutEffect, useRef, useState } from 'react';
import { clamp } from 'utils/numbers/clamp';
import { twMergeCustom } from 'utils/twMerge';

export type MediaCarouselItem = TypeImageFragment | TypeVideoTokenFragment | TypeFileFragment;

export type MediaCarouselTrackHandle = {
    scrollToIndex: (index: number, behavior?: ScrollBehavior) => void;
};

type MediaCarouselTrackProps = {
    items: MediaCarouselItem[];
    selectedIndex: number;
    initialIndex?: number;
    ariaLabel: string;
    className?: string;
    itemClassName?: string;
    style?: React.CSSProperties;
    renderItem: (
        item: MediaCarouselItem,
        index: number,
        isLoaded: boolean,
        isSelected: boolean,
        isTrackScrolling: boolean,
    ) => React.ReactNode;
    onSelectedIndexChange: (index: number) => void;
    onTrackScroll?: () => void;
};

const SCROLL_SETTLE_DELAY_MS = 100;

const getItemKey = (item: MediaCarouselItem, index: number) => {
    const itemIdentifier = item.__typename === 'VideoToken' ? item.token : item.url;

    return `${item.__typename}-${itemIdentifier}-${index}`;
};

export const MediaCarouselTrack = forwardRef<MediaCarouselTrackHandle, MediaCarouselTrackProps>(
    (
        {
            items,
            selectedIndex,
            initialIndex = 0,
            ariaLabel,
            className,
            itemClassName,
            style,
            renderItem,
            onSelectedIndexChange,
            onTrackScroll,
        },
        ref,
    ) => {
        const normalizedInitialIndex = items.length > 0 ? clamp(initialIndex, 0, items.length - 1) : 0;
        const [loadedItemIndexes, setLoadedItemIndexes] = useState<Set<number>>(() =>
            items.length > 0 ? new Set([normalizedInitialIndex]) : new Set(),
        );
        const trackRef = useRef<HTMLUListElement>(null);
        const trackWidthRef = useRef(0);
        const lastScrollLeftRef = useRef(0);
        const programmaticTargetIndexRef = useRef<number | null>(null);
        const selectedIndexRef = useRef(selectedIndex);
        const scrollSettleTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
        const [isTrackScrolling, setIsTrackScrolling] = useState(false);

        selectedIndexRef.current = selectedIndex;

        const loadItem = useCallback((index: number) => {
            setLoadedItemIndexes((currentLoadedItemIndexes) => {
                if (currentLoadedItemIndexes.has(index)) {
                    return currentLoadedItemIndexes;
                }

                const nextLoadedItemIndexes = new Set(currentLoadedItemIndexes);
                nextLoadedItemIndexes.add(index);

                return nextLoadedItemIndexes;
            });
        }, []);

        const settleScrollPosition = useCallback(() => {
            const track = trackRef.current;

            if (track === null || track.clientWidth === 0 || items.length === 0) {
                programmaticTargetIndexRef.current = null;
                setIsTrackScrolling(false);

                return;
            }

            const nearestItemIndex =
                programmaticTargetIndexRef.current ??
                clamp(Math.round(track.scrollLeft / track.clientWidth), 0, items.length - 1);
            const settledScrollLeft = nearestItemIndex * track.clientWidth;

            programmaticTargetIndexRef.current = null;
            loadItem(nearestItemIndex);

            if (nearestItemIndex !== selectedIndexRef.current) {
                selectedIndexRef.current = nearestItemIndex;
                onSelectedIndexChange(nearestItemIndex);
            }

            if (Math.abs(track.scrollLeft - settledScrollLeft) > 0.5) {
                track.scrollLeft = settledScrollLeft;
            }

            lastScrollLeftRef.current = settledScrollLeft;
            setIsTrackScrolling(false);
        }, [items.length, loadItem, onSelectedIndexChange]);

        const scheduleScrollSettle = useCallback(() => {
            if (scrollSettleTimeoutRef.current !== null) {
                clearTimeout(scrollSettleTimeoutRef.current);
            }

            scrollSettleTimeoutRef.current = setTimeout(settleScrollPosition, SCROLL_SETTLE_DELAY_MS);
        }, [settleScrollPosition]);

        const scrollToIndex = useCallback(
            (index: number, behavior: ScrollBehavior = 'smooth') => {
                if (items.length === 0) {
                    return;
                }

                const normalizedIndex = clamp(index, 0, items.length - 1);
                const track = trackRef.current;

                loadItem(normalizedIndex);
                programmaticTargetIndexRef.current = normalizedIndex;
                selectedIndexRef.current = normalizedIndex;
                onSelectedIndexChange(normalizedIndex);
                setIsTrackScrolling(true);
                track?.scrollTo?.({ behavior, left: normalizedIndex * track.clientWidth });
                scheduleScrollSettle();
            },
            [items.length, loadItem, onSelectedIndexChange, scheduleScrollSettle],
        );

        useImperativeHandle(ref, () => ({ scrollToIndex }), [scrollToIndex]);

        useLayoutEffect(() => {
            const track = trackRef.current;

            if (track === null || items.length === 0) {
                return;
            }

            const initialScrollLeft = normalizedInitialIndex * track.clientWidth;
            trackWidthRef.current = track.clientWidth;
            track.scrollLeft = initialScrollLeft;
            lastScrollLeftRef.current = initialScrollLeft;
        }, [items.length, normalizedInitialIndex]);

        useEffect(() => {
            const track = trackRef.current;

            if (track === null || typeof ResizeObserver === 'undefined') {
                return undefined;
            }

            const resizeObserver = new ResizeObserver(() => {
                const currentTrackWidth = track.clientWidth;

                if (currentTrackWidth === 0 || currentTrackWidth === trackWidthRef.current) {
                    return;
                }

                const alignedScrollLeft = selectedIndexRef.current * currentTrackWidth;

                trackWidthRef.current = currentTrackWidth;
                track.scrollLeft = alignedScrollLeft;
                lastScrollLeftRef.current = alignedScrollLeft;
            });

            resizeObserver.observe(track);

            return () => resizeObserver.disconnect();
        }, []);

        useEffect(
            () => () => {
                if (scrollSettleTimeoutRef.current !== null) {
                    clearTimeout(scrollSettleTimeoutRef.current);
                }
            },
            [],
        );

        const handleScroll = () => {
            const track = trackRef.current;

            if (track === null || track.clientWidth === 0 || items.length === 0) {
                return;
            }

            const lastItemIndex = items.length - 1;
            const itemPosition = track.scrollLeft / track.clientWidth;
            const isMovingForward = track.scrollLeft >= lastScrollLeftRef.current;
            const programmaticTargetIndex = programmaticTargetIndexRef.current;
            const itemToLoad =
                programmaticTargetIndex ??
                clamp(isMovingForward ? Math.ceil(itemPosition) : Math.floor(itemPosition), 0, lastItemIndex);
            const nearestItemIndex = clamp(Math.round(itemPosition), 0, lastItemIndex);

            setIsTrackScrolling(true);
            loadItem(itemToLoad);

            if (programmaticTargetIndex === null && nearestItemIndex !== selectedIndexRef.current) {
                selectedIndexRef.current = nearestItemIndex;
                onSelectedIndexChange(nearestItemIndex);
            }

            lastScrollLeftRef.current = track.scrollLeft;
            scheduleScrollSettle();
            onTrackScroll?.();
        };

        return (
            <ul
                aria-label={ariaLabel}
                className={twMergeCustom(
                    'hide-scrollbar grid w-full max-w-full snap-x snap-mandatory auto-cols-[100%] grid-flow-col overflow-x-auto overscroll-x-contain',
                    className,
                )}
                ref={trackRef}
                style={style}
                tabIndex={-1}
                onScroll={handleScroll}
            >
                {items.map((item, index) => {
                    const isSelected = index === selectedIndex;

                    return (
                        <li
                            key={getItemKey(item, index)}
                            aria-hidden={!isSelected}
                            className={twMergeCustom(
                                'flex w-full snap-center items-center justify-center px-2',
                                itemClassName,
                            )}
                        >
                            {renderItem(item, index, loadedItemIndexes.has(index), isSelected, isTrackScrolling)}
                        </li>
                    );
                })}
            </ul>
        );
    },
);

MediaCarouselTrack.displayName = 'MediaCarouselTrack';
