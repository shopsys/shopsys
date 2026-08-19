import { type RefObject, useCallback, useEffect, useState } from 'react';
import { isClient } from 'utils/isClient';
import { useMediaMin } from 'utils/ui/useMediaMin';

const FIXED_HEADER_HEIGHT_PROPERTY = '--fixed-header-height';
const STICKY_NAVIGATION_OFFSET_PROPERTY = '--sticky-navigation-offset';

export const useDesktopFixedHeader = (headerRef: RefObject<HTMLElement | null>) => {
    const isDesktop = useMediaMin('vl');
    const [fixedHeaderElement, setFixedHeaderElement] = useState<HTMLDivElement | null>(null);
    const [fixedHeaderHeight, setFixedHeaderHeight] = useState(0);
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
        if (!isDesktop || !fixedHeaderElement) {
            setFixedHeaderHeight(0);
            document.documentElement.style.removeProperty(FIXED_HEADER_HEIGHT_PROPERTY);

            return undefined;
        }

        const updateFixedHeaderHeight = () => {
            const nextFixedHeaderHeight = fixedHeaderElement.getBoundingClientRect().height;
            setFixedHeaderHeight(nextFixedHeaderHeight);
            document.documentElement.style.setProperty(FIXED_HEADER_HEIGHT_PROPERTY, `${nextFixedHeaderHeight}px`);
        };

        updateFixedHeaderHeight();

        if (typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(updateFixedHeaderHeight);
            resizeObserver.observe(fixedHeaderElement);

            return () => {
                resizeObserver.disconnect();
                document.documentElement.style.removeProperty(FIXED_HEADER_HEIGHT_PROPERTY);
            };
        }

        window.addEventListener('resize', updateFixedHeaderHeight);

        return () => {
            window.removeEventListener('resize', updateFixedHeaderHeight);
            document.documentElement.style.removeProperty(FIXED_HEADER_HEIGHT_PROPERTY);
        };
    }, [fixedHeaderElement, isDesktop]);

    useEffect(() => {
        if (!showDesktopFixedHeader || fixedHeaderHeight === 0) {
            document.documentElement.style.removeProperty(STICKY_NAVIGATION_OFFSET_PROPERTY);

            return undefined;
        }

        document.documentElement.style.setProperty(STICKY_NAVIGATION_OFFSET_PROPERTY, `${fixedHeaderHeight}px`);

        return () => {
            document.documentElement.style.removeProperty(STICKY_NAVIGATION_OFFSET_PROPERTY);
        };
    }, [fixedHeaderHeight, showDesktopFixedHeader]);

    return { fixedHeaderHeight, fixedHeaderRef, isDesktop, isFixedHeaderVisible, showDesktopFixedHeader };
};
