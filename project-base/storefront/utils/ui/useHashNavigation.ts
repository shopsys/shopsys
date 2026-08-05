import { RefObject, useEffect, useEffectEvent, useRef, useState } from 'react';

type SectionRef = {
    id: string;
    ref: RefObject<HTMLElement | null>;
};

type UseHashNavigationReturn = {
    scrollToSection: (sectionId: string) => void;
    activeSection: string | null;
};

export const useHashNavigation = (sections: SectionRef[]): UseHashNavigationReturn => {
    const [activeSection, setActiveSection] = useState<string | null>(null);
    const isUserScrollingRef = useRef(true);

    const updateHash = (sectionId: string | null) => {
        const url = new URL(window.location.href);
        url.hash = sectionId ?? '';
        window.history.replaceState(null, '', url);
    };

    const scrollToSection = (sectionId: string) => {
        const section = sections.find((s) => s.id === sectionId);

        if (section?.ref.current) {
            // Disable scroll detection during programmatic scroll
            isUserScrollingRef.current = false;

            setActiveSection(sectionId);
            updateHash(sectionId);
            section.ref.current.scrollIntoView({ behavior: 'smooth' });
        }
    };

    const onSectionAnchorClick = useEffectEvent((event: MouseEvent) => {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey ||
            !(event.target instanceof Element)
        ) {
            return;
        }

        const anchor = event.target.closest<HTMLAnchorElement>('a[href^="#"]');
        const sectionId = anchor?.hash.slice(1);

        if (!sectionId || !sections.some((section) => section.id === sectionId)) {
            return;
        }

        // Prevent native fragment scrolling from racing with the scroll-based section detection.
        event.preventDefault();
        scrollToSection(sectionId);
    });

    useEffect(() => {
        document.addEventListener('click', onSectionAnchorClick);

        return () => document.removeEventListener('click', onSectionAnchorClick);
    }, []);

    // Handle initial hash and same-page anchor navigation
    useEffect(() => {
        let animationFrameId: number | null = null;

        const scrollToCurrentHash = () => {
            const hash = window.location.hash.slice(1);

            if (!hash) {
                return;
            }

            const section = sections.find(({ id }) => id === hash);

            if (!section?.ref.current) {
                return;
            }

            setActiveSection(hash);
            isUserScrollingRef.current = false;

            if (animationFrameId !== null) {
                window.cancelAnimationFrame(animationFrameId);
            }

            animationFrameId = window.requestAnimationFrame(() => {
                section.ref.current?.scrollIntoView({ block: 'start' });
                animationFrameId = null;
            });
        };

        scrollToCurrentHash();
        window.addEventListener('hashchange', scrollToCurrentHash);

        return () => {
            window.removeEventListener('hashchange', scrollToCurrentHash);

            if (animationFrameId !== null) {
                window.cancelAnimationFrame(animationFrameId);
            }
        };
    }, []);

    // Detect user scroll (wheel/touch) to re-enable scroll detection
    useEffect(() => {
        const enableScrollDetection = () => {
            isUserScrollingRef.current = true;
        };

        window.addEventListener('wheel', enableScrollDetection, { passive: true });
        window.addEventListener('touchmove', enableScrollDetection, { passive: true });

        return () => {
            window.removeEventListener('wheel', enableScrollDetection);
            window.removeEventListener('touchmove', enableScrollDetection);
        };
    }, []);

    // Scroll-based section detection - only when user is scrolling
    const onScroll = useEffectEvent(() => {
        if (!isUserScrollingRef.current) {
            return;
        }

        let active: string | null = null;

        for (const section of sections) {
            const sectionElement = section.ref.current;
            const rect = sectionElement?.getBoundingClientRect();
            const scrollOffset = sectionElement
                ? Number.parseFloat(window.getComputedStyle(sectionElement).scrollMarginTop) || 0
                : 0;

            if (rect && rect.top <= scrollOffset) {
                active = section.id;
            }
        }

        setActiveSection((current) => {
            if (active !== current) {
                updateHash(active);
                return active;
            }
            return current;
        });
    });

    useEffect(() => {
        let rafId: number | null = null;

        const throttledOnScroll = () => {
            if (rafId !== null) {
                return;
            }
            rafId = requestAnimationFrame(() => {
                onScroll();
                rafId = null;
            });
        };

        window.addEventListener('scroll', throttledOnScroll, { passive: true });

        return () => {
            window.removeEventListener('scroll', throttledOnScroll);
            if (rafId !== null) {
                cancelAnimationFrame(rafId);
            }
        };
    }, []);

    return {
        scrollToSection,
        activeSection,
    };
};
