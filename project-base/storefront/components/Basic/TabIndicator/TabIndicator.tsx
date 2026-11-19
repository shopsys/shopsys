import { type RefObject, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type TabIndicatorProps = {
    containerRef: RefObject<HTMLElement | null>;
    // changes whenever another tab becomes the current one, so the indicator moves to it
    activeKey: string | null;
};

type IndicatorPosition = {
    left: number;
    top: number;
    width: number;
};

const CURRENT_TAB_SELECTOR = '[aria-current]:not([aria-current="false"]), [aria-pressed="true"]';
// the line is as wide as the label, not the padding of the tab (px-1)
const TAB_INSET = 4;
const INDICATOR_HEIGHT = 3;

/**
 * The line under the current tab, shared by a row of tabs (getSortingTabClassName), so it slides to a newly current tab
 * instead of jumping. Render it inside the row, which needs `relative`; the current tab is the one with `aria-current`
 * or `aria-pressed="true"`.
 */
export const TabIndicator: FC<TabIndicatorProps> = ({ containerRef, activeKey }) => {
    const [position, setPosition] = useState<IndicatorPosition | null>(null);
    const [canAnimate, setCanAnimate] = useState(false);

    // a passive effect, because the row's ref is attached only after the layout effects of its children have run
    useEffect(() => {
        const container = containerRef.current;

        if (!container) {
            return undefined;
        }

        const measureCurrentTab = () => {
            const currentTab = container.querySelector<HTMLElement>(CURRENT_TAB_SELECTOR);

            setPosition(
                currentTab
                    ? {
                          left: currentTab.offsetLeft + TAB_INSET,
                          top: currentTab.offsetTop + currentTab.offsetHeight - INDICATOR_HEIGHT,
                          width: currentTab.offsetWidth - 2 * TAB_INSET,
                      }
                    : null,
            );
        };

        measureCurrentTab();

        if (typeof ResizeObserver === 'undefined') {
            return undefined;
        }

        const resizeObserver = new ResizeObserver(measureCurrentTab);
        resizeObserver.observe(container);

        return () => resizeObserver.disconnect();
    }, [containerRef, activeKey]);

    // the first position is placed without a transition, so the line does not slide in from the start of the row
    useEffect(() => {
        if (!position || canAnimate) {
            return undefined;
        }

        const animationFrameId = window.requestAnimationFrame(() => setCanAnimate(true));

        return () => window.cancelAnimationFrame(animationFrameId);
    }, [position, canAnimate]);

    if (!position) {
        return null;
    }

    return (
        <span
            aria-hidden="true"
            className={twJoin(
                'pointer-events-none absolute top-0 left-0 h-0.75 rounded-full bg-link-default',
                canAnimate && 'transition-[transform,width] duration-300 ease-out motion-reduce:transition-none',
            )}
            style={{ transform: `translate(${position.left}px, ${position.top}px)`, width: position.width }}
        />
    );
};
