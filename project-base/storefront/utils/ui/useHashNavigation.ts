import { RefObject, useEffect, useEffectEvent, useRef, useState } from 'react';

type SectionRef = {
    id: string;
    ref: RefObject<HTMLElement | null>;
};

type UseHashNavigationReturn = {
    scrollToSection: (sectionId: string) => void;
    activeSection: string | null;
};

const SCROLL_OFFSET = 150;

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

    // Handle initial hash
    useEffect(() => {
        const hash = window.location.hash.slice(1);

        if (hash) {
            setActiveSection(hash);
        }
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
            const rect = section.ref.current?.getBoundingClientRect();

            if (rect && rect.top <= SCROLL_OFFSET) {
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
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => {
            window.removeEventListener('scroll', onScroll);
        };
    }, []);

    return {
        scrollToSection,
        activeSection,
    };
};
