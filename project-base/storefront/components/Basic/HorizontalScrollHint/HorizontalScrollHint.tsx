import { ReactNode, useCallback, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type HorizontalScrollHintProps = {
    render: (scrollContainerRef: React.RefObject<HTMLDivElement | null>) => ReactNode;
};

export const HorizontalScrollHint: FC<HorizontalScrollHintProps> = ({ render }) => {
    const scrollContainerRef = useRef<HTMLDivElement>(null);
    const [showLeftHint, setShowLeftHint] = useState(false);
    const [showRightHint, setShowRightHint] = useState(false);

    const updateHints = useCallback(() => {
        const scrollContainer = scrollContainerRef.current;

        if (scrollContainer === null) {
            return;
        }

        const { clientWidth, scrollLeft, scrollWidth } = scrollContainer;

        setShowLeftHint(scrollLeft > 1);
        setShowRightHint(scrollWidth - clientWidth - scrollLeft > 1);
    }, []);

    useEffect(() => {
        const scrollContainer = scrollContainerRef.current;

        if (scrollContainer === null) {
            return undefined;
        }

        const resizeObserver = new ResizeObserver(updateHints);

        updateHints();
        resizeObserver.observe(scrollContainer);
        scrollContainer.addEventListener('scroll', updateHints, { passive: true });

        return () => {
            resizeObserver.disconnect();
            scrollContainer.removeEventListener('scroll', updateHints);
        };
    }, [updateHints]);

    return (
        <div
            className={twJoin(
                'relative min-w-0',
                showLeftHint &&
                    'before:pointer-events-none before:absolute before:top-0 before:bottom-0 before:left-0 before:z-above before:w-8 before:bg-linear-to-r before:from-background-default before:to-transparent before:content-[""]',
                showRightHint &&
                    'after:pointer-events-none after:absolute after:top-0 after:right-0 after:bottom-0 after:z-above after:w-8 after:bg-linear-to-l after:from-background-default after:to-transparent after:content-[""]',
            )}
        >
            {render(scrollContainerRef)}
        </div>
    );
};
