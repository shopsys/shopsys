import { HorizontalScrollHint } from 'components/Basic/HorizontalScrollHint/HorizontalScrollHint';
import { TabIndicator } from 'components/Basic/TabIndicator/TabIndicator';
import { getSortingTabClassName } from 'components/Blocks/SortingBar/sortingBarUtils';
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
    const navigationRef = useRef<HTMLDivElement>(null);
    const sentinelRef = useRef<HTMLDivElement>(null);
    const [isNavigationSticky, setIsNavigationSticky] = useState(false);
    const [isBeforeStickyActionBoundary, setIsBeforeStickyActionBoundary] = useState(true);
    const isDesktop = useMediaMin('vl');
    const isSectionNavigationVisible = sections.length > 1;
    // before the first section is scrolled to, the first tab is the current one, so the bar reads as tabs right away
    const currentSection = activeSection ?? sections[0]?.id ?? null;

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
            <div
                className={twMergeCustom(
                    'sticky top-(--sticky-navigation-offset,0px) z-menu bg-background-default transition-[top,box-shadow] duration-200',
                    isNavigationSticky && 'shadow-md',
                    !isSectionNavigationVisible && 'h-0',
                )}
                ref={navigationRef}
            >
                {/* from xl the tabs start where the section content does (its 50rem column) and the sticky action takes
                    the space on the right; narrower screens keep them at the left edge, so they fit next to the action */}
                <Webline
                    className={twMergeCustom(
                        'flex items-center gap-6',
                        isSectionNavigationVisible &&
                            'xl:grid xl:grid-cols-[minmax(0,1fr)_minmax(0,50rem)_minmax(0,1fr)] xl:gap-0',
                        !isSectionNavigationVisible &&
                            'absolute inset-x-0 top-0 justify-end bg-background-default shadow-md',
                        !isSectionNavigationVisible && (!product || !isDesktop || !isNavigationSticky) && 'hidden',
                    )}
                >
                    <div
                        className={twMergeCustom(
                            'flex min-w-0 flex-1 items-center gap-6 xl:col-span-2 xl:col-start-2',
                            !isSectionNavigationVisible && 'justify-end',
                        )}
                    >
                        {isSectionNavigationVisible && (
                            <nav
                                className="-ml-1 min-w-0 flex-1 xl:max-w-[calc(50rem+0.25rem)]"
                                data-tid={TIDs.product_detail_section_navigation}
                            >
                                <HorizontalScrollHint
                                    render={(scrollContainerRef) => (
                                        <div
                                            ref={scrollContainerRef}
                                            className="hide-scrollbar relative flex gap-6 overflow-x-auto"
                                        >
                                            {sections.map((section) => {
                                                const isActive = currentSection === section.id;

                                                return (
                                                    <button
                                                        key={section.id}
                                                        aria-current={isActive ? 'location' : undefined}
                                                        type="button"
                                                        className={twMergeCustom(
                                                            getSortingTabClassName({ isActive }),
                                                            'min-h-12 vl:min-h-14 shrink-0 whitespace-nowrap',
                                                        )}
                                                        ref={(element) => {
                                                            if (element) {
                                                                buttonRefs.current.set(section.id, element);
                                                            } else {
                                                                buttonRefs.current.delete(section.id);
                                                            }
                                                        }}
                                                        onClick={() => onSectionClick(section.id)}
                                                    >
                                                        {section.label}
                                                    </button>
                                                );
                                            })}

                                            <TabIndicator
                                                activeKey={currentSection}
                                                containerRef={scrollContainerRef}
                                            />
                                        </div>
                                    )}
                                />
                            </nav>
                        )}

                        {product && isDesktop && (
                            <ProductDetailStickyAction
                                isVisible={isNavigationSticky}
                                placement="inline"
                                product={product}
                            />
                        )}
                    </div>
                </Webline>
            </div>

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
