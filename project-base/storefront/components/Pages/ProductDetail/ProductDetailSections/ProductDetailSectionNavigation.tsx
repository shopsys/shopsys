import { HorizontalScrollHint } from 'components/Basic/HorizontalScrollHint/HorizontalScrollHint';
import { Tag } from 'components/Basic/Tag/Tag';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { type RefObject, useEffect, useRef, useState } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { ProductDetailStickyAction } from './ProductDetailStickyAction';

const STICKY_NAVIGATION_OFFSET_PROPERTY = '--sticky-navigation-offset';
const STICKY_ACTION_BOUNDARY_HYSTERESIS = 80;

type SectionButton = {
    id: string;
    label: string;
};

type ProductDetailSectionNavigationProps = {
    sections: SectionButton[];
    onSectionClick: (sectionId: string) => void;
    activeSection: string | null;
    product?: TypeProductDetailFragment;
    stickyActionBoundaryRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailSectionNavigation: FC<ProductDetailSectionNavigationProps> = ({
    sections,
    onSectionClick,
    activeSection,
    product,
    stickyActionBoundaryRef,
}) => {
    const buttonRefs = useRef<Map<string, HTMLButtonElement>>(new Map());
    const navigationRef = useRef<HTMLElement>(null);
    const sentinelRef = useRef<HTMLDivElement>(null);
    const [isNavigationSticky, setIsNavigationSticky] = useState(false);
    const [isBeforeStickyActionBoundary, setIsBeforeStickyActionBoundary] = useState(true);
    const isDesktop = useMediaMin('vl');

    useEffect(() => {
        let animationFrameId: number | null = null;

        const updateStickyState = () => {
            if (animationFrameId !== null) {
                return;
            }

            animationFrameId = window.requestAnimationFrame(() => {
                const navigationElement = navigationRef.current;
                const sentinelElement = sentinelRef.current;
                const stickyActionBoundaryElement = stickyActionBoundaryRef.current;

                if (navigationElement && sentinelElement) {
                    const stickyOffset =
                        Number.parseFloat(
                            window
                                .getComputedStyle(navigationElement)
                                .getPropertyValue(STICKY_NAVIGATION_OFFSET_PROPERTY),
                        ) || 0;
                    setIsNavigationSticky(sentinelElement.getBoundingClientRect().top <= stickyOffset);
                }

                if (stickyActionBoundaryElement) {
                    const stickyActionBoundaryTop = stickyActionBoundaryElement.getBoundingClientRect().top;

                    setIsBeforeStickyActionBoundary((wasBeforeStickyActionBoundary) => {
                        const boundaryHysteresis = wasBeforeStickyActionBoundary
                            ? 0
                            : STICKY_ACTION_BOUNDARY_HYSTERESIS;

                        return stickyActionBoundaryTop > window.innerHeight + boundaryHysteresis;
                    });
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
    }, [stickyActionBoundaryRef]);

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
                data-tid={TIDs.product_detail_section_navigation}
                ref={navigationRef}
            >
                <Webline className="flex items-center gap-6 py-4">
                    <div className="min-w-0 flex-1">
                        <HorizontalScrollHint
                            render={(scrollContainerRef) => (
                                <div ref={scrollContainerRef} className="flex gap-3 overflow-x-auto">
                                    {sections.map((section) => (
                                        <Tag
                                            key={section.id}
                                            buttonRef={(el) => {
                                                if (el) {
                                                    buttonRefs.current.set(section.id, el);
                                                } else {
                                                    buttonRefs.current.delete(section.id);
                                                }
                                            }}
                                            isActive={activeSection === section.id}
                                            onClick={() => onSectionClick(section.id)}
                                            className="shrink-0"
                                        >
                                            {section.label}
                                        </Tag>
                                    ))}
                                </div>
                            )}
                        />
                    </div>

                    {product && isDesktop && (
                        <ProductDetailStickyAction
                            isVisible={isNavigationSticky}
                            placement="inline"
                            product={product}
                        />
                    )}
                </Webline>
            </nav>

            {product && isDesktop === false && (
                <ProductDetailStickyAction
                    isVisible={isNavigationSticky && isBeforeStickyActionBoundary}
                    placement="floating"
                    product={product}
                />
            )}
        </>
    );
};
