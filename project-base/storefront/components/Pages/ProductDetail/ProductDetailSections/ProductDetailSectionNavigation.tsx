import { HorizontalScrollHint } from 'components/Basic/HorizontalScrollHint/HorizontalScrollHint';
import { Tag } from 'components/Basic/Tag/Tag';
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
                    'sticky top-0 z-menu bg-background-default transition-shadow duration-200',
                    !isIntersectingSentinel && 'shadow-md',
                )}
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
