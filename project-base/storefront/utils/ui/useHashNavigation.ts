import { RefObject, useCallback, useEffect, useRef, useState } from 'react';

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

    const updateHash = useCallback((sectionId: string | null) => {
        const url = new URL(window.location.href);
        url.hash = sectionId ?? '';
        window.history.replaceState(null, '', url);
    }, []);

    const scrollToSection = useCallback(
        (sectionId: string) => {
            const section = sections.find((s) => s.id === sectionId);

            if (section?.ref.current) {
                // Disable scroll detection during programmatic scroll
                isUserScrollingRef.current = false;

                setActiveSection(sectionId);
                updateHash(sectionId);
                section.ref.current.scrollIntoView({ behavior: 'smooth' });
            }
        },
        [sections, updateHash],
    );

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
    useEffect(() => {
        const findActiveSection = (): string | null => {
            let active: string | null = null;

            for (const section of sections) {
                const rect = section.ref.current?.getBoundingClientRect();

                if (rect && rect.top <= SCROLL_OFFSET) {
                    active = section.id;
                }
            }

            return active;
        };

        const handleScroll = () => {
            if (!isUserScrollingRef.current) {
                return;
            }

            const newActive = findActiveSection();

            setActiveSection((current) => {
                if (newActive !== current) {
                    updateHash(newActive);
                    return newActive;
                }
                return current;
            });
        };

        window.addEventListener('scroll', handleScroll, { passive: true });

        return () => {
            window.removeEventListener('scroll', handleScroll);
        };
    }, [sections, updateHash]);

    return {
        scrollToSection,
        activeSection,
    };
};
