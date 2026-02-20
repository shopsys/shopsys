import { Webline } from 'components/Layout/Webline/Webline';
import { useEffect, useRef } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { useIntersectionObserver } from 'utils/ui/useIntersectionObserver';

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
    const { ref: sentinelRef, isIntersecting: isIntersectingSentinel } = useIntersectionObserver({
        defaultIsIntersecting: true,
    });

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
                    'z-menu bg-background-default sticky top-0 transition-shadow duration-200',
                    !isIntersectingSentinel && 'shadow-md',
                )}
            >
                <Webline className="after:from-background-default flex gap-3 overflow-x-auto py-4 after:pointer-events-none after:absolute after:top-0 after:right-0 after:h-full after:w-14 after:bg-linear-to-l after:to-transparent after:content-[''] after:lg:hidden">
                    {sections.map((section) => (
                        <button
                            key={section.id}
                            type="button"
                            className={twMergeCustom(
                                'font-secondary shrink-0 cursor-pointer rounded-2xl px-3.5 py-1.5 text-sm font-semibold select-none',
                                'focus-visible:text-text-default focus-visible:bg-orange-500 focus-visible:outline-hidden',
                                activeSection === section.id
                                    ? 'bg-background-default outline-border-success outline-2'
                                    : 'bg-background-more hover:bg-background-most',
                            )}
                            ref={(el) => {
                                if (el) {
                                    buttonRefs.current.set(section.id, el);
                                }
                            }}
                            onClick={() => onSectionClick(section.id)}
                        >
                            {section.label}
                        </button>
                    ))}
                </Webline>
            </nav>
        </>
    );
};
