import { type RefObject, useCallback, useEffect, useState } from 'react';
import { isClient } from 'utils/isClient';
import { useMediaMin } from 'utils/ui/useMediaMin';

const STICKY_NAVIGATION_OFFSET_PROPERTY = '--sticky-navigation-offset';

export const useDesktopFixedHeader = (headerRef: RefObject<HTMLElement | null>) => {
    const isDesktop = useMediaMin('vl');
    const [fixedHeaderElement, setFixedHeaderElement] = useState<HTMLDivElement | null>(null);
    const [showDesktopFixedHeader, setShowDesktopFixedHeader] = useState(false);
    const [isFixedHeaderVisible, setIsFixedHeaderVisible] = useState(false);
    const fixedHeaderRef = useCallback((element: HTMLDivElement | null) => {
        setFixedHeaderElement(element);
    }, []);

    useEffect(() => {
        if (!isDesktop) {
            setShowDesktopFixedHeader(false);

            return undefined;
        }

        if (!isClient || typeof IntersectionObserver === 'undefined') {
            return undefined;
        }

        const headerElement = headerRef.current;

        if (!headerElement) {
            return undefined;
        }

        const observer = new IntersectionObserver(([entry]) => {
            setShowDesktopFixedHeader(!entry.isIntersecting);
        });

        observer.observe(headerElement);

        return () => {
            observer.disconnect();
        };
    }, [headerRef, isDesktop]);

    useEffect(() => {
        if (!showDesktopFixedHeader) {
            setIsFixedHeaderVisible(false);

            return undefined;
        }

        const animationFrameId = window.requestAnimationFrame(() => setIsFixedHeaderVisible(true));

        return () => {
            window.cancelAnimationFrame(animationFrameId);
        };
    }, [showDesktopFixedHeader]);

    useEffect(() => {
        if (!showDesktopFixedHeader || !fixedHeaderElement) {
            document.documentElement.style.removeProperty(STICKY_NAVIGATION_OFFSET_PROPERTY);

            return undefined;
        }

        const updateStickyNavigationOffset = () => {
            const fixedHeaderHeight = fixedHeaderElement.getBoundingClientRect().height;
            document.documentElement.style.setProperty(STICKY_NAVIGATION_OFFSET_PROPERTY, `${fixedHeaderHeight}px`);
        };

        updateStickyNavigationOffset();

        if (typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(updateStickyNavigationOffset);
            resizeObserver.observe(fixedHeaderElement);

            return () => {
                resizeObserver.disconnect();
                document.documentElement.style.removeProperty(STICKY_NAVIGATION_OFFSET_PROPERTY);
            };
        }

        window.addEventListener('resize', updateStickyNavigationOffset);

        return () => {
            window.removeEventListener('resize', updateStickyNavigationOffset);
            document.documentElement.style.removeProperty(STICKY_NAVIGATION_OFFSET_PROPERTY);
        };
    }, [fixedHeaderElement, showDesktopFixedHeader]);

    return { fixedHeaderRef, isDesktop, isFixedHeaderVisible, showDesktopFixedHeader };
};
