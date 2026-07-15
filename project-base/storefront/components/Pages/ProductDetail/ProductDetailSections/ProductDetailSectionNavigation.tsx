import { HorizontalScrollHint } from 'components/Basic/HorizontalScrollHint/HorizontalScrollHint';
import { Tag } from 'components/Basic/Tag/Tag';
import { Webline } from 'components/Layout/Webline/Webline';
import { useEffect, useRef, useState } from 'react';
import { twMergeCustom } from 'utils/twMerge';

const STICKY_NAVIGATION_OFFSET_PROPERTY = '--sticky-navigation-offset';

type SectionButton = {
    id: string;
    label: string;
};

type ProductDetailSectionNavigationProps = {
    sections: SectionButton[];
    onSectionClick: (sectionId: string) => void;
    activeSection: string | null;
};

export const ProductDetailSectionNavigation: FC<ProductDetailSectionNavigationProps> = ({
    sections,
    onSectionClick,
    activeSection,
}) => {
    const buttonRefs = useRef<Map<string, HTMLButtonElement>>(new Map());
    const navigationRef = useRef<HTMLElement>(null);
    const sentinelRef = useRef<HTMLDivElement>(null);
    const [isNavigationSticky, setIsNavigationSticky] = useState(false);

    useEffect(() => {
        let animationFrameId: number | null = null;

        const updateStickyState = () => {
            if (animationFrameId !== null) {
                return;
            }

            animationFrameId = window.requestAnimationFrame(() => {
                const navigationElement = navigationRef.current;
                const sentinelElement = sentinelRef.current;

                if (navigationElement && sentinelElement) {
                    const stickyOffset =
                        Number.parseFloat(
                            window
                                .getComputedStyle(navigationElement)
                                .getPropertyValue(STICKY_NAVIGATION_OFFSET_PROPERTY),
                        ) || 0;
                    setIsNavigationSticky(sentinelElement.getBoundingClientRect().top <= stickyOffset);
                }

                animationFrameId = null;
            });
        };

        updateStickyState();
        window.addEventListener('resize', updateStickyState);
        window.addEventListener('scroll', updateStickyState, { passive: true });
        const stickyOffsetObserver = new MutationObserver(updateStickyState);
        stickyOffsetObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['style'] });

        return () => {
            window.removeEventListener('resize', updateStickyState);
            window.removeEventListener('scroll', updateStickyState);
            stickyOffsetObserver.disconnect();

            if (animationFrameId !== null) {
                window.cancelAnimationFrame(animationFrameId);
            }
        };
    }, []);

    useEffect(() => {
        const button = activeSection ? buttonRefs.current.get(activeSection) : null;
        const container = button?.parentElement;

        if (button && container) {
            const scrollLeft = button.offsetLeft - container.clientWidth / 2 + button.clientWidth / 2;
            container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
        }
    }, [activeSection]);

    return (
        <>
            <div aria-hidden="true" className="h-0" ref={sentinelRef} />
            <nav
                className={twMergeCustom(
                    'sticky top-(--sticky-navigation-offset,0px) z-menu bg-background-default transition-[top,box-shadow] duration-200',
                    isNavigationSticky && 'shadow-md',
                )}
                ref={navigationRef}
            >
                <Webline className="py-4">
                    <HorizontalScrollHint
                        render={(scrollContainerRef) => (
                            <div ref={scrollContainerRef} className="flex gap-3 overflow-x-auto">
                                {sections.map((section) => (
                                    <Tag
                                        key={section.id}
                                        buttonRef={(el) => {
                                            if (el) {
                                                buttonRefs.current.set(section.id, el);
                                            }
                                        }}
                                        isActive={activeSection === section.id}
                                        onClick={() => onSectionClick(section.id)}
                                    >
                                        {section.label}
                                    </Tag>
                                ))}
                            </div>
                        )}
                    />
                </Webline>
            </nav>
        </>
    );
};
