import { AnimateNavigationMenu } from 'components/Basic/Animations/AnimateNavigationMenu';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { AnimatePresence, m, useReducedMotion } from 'framer-motion';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import type { FocusEventHandler } from 'react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useDebounce } from 'utils/useDebounce';
import { NavigationItem } from './NavigationItem';
import { NavigationItemColumn } from './NavigationItemColumn';
import { NavigationMoreItem } from './NavigationMoreItem';
import { NavigationMoreMenu } from './NavigationMoreMenu';
import {
    getNavigationItemKey,
    getNavigationItemMenuId,
    getNavigationItemSkeletonType,
    isNavigationItemWithCategories,
} from './navigationUtils';
import { useNavigationOverflow } from './useNavigationOverflow';

const HOVER_MENU_DELAY = 400;

export type NavigationProps = {
    navigation: TypeCategoriesByColumnFragment[];
    id?: string;
    itemClassName?: string;
};

export const Navigation: FC<NavigationProps> = ({ navigation, id = 'main-navigation', itemClassName }) => {
    const { t } = useTranslation();
    const shouldReduceMotion = useReducedMotion();
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);
    const [isFirstHover, setIsFirstHover] = useState(false);
    const [isAnimationDisabled, setIsAnimationDisabled] = useState(false);
    const [activeNavigationItemKey, setActiveNavigationItemKey] = useState<string | null>(null);
    const [navigationOverlayTop, setNavigationOverlayTop] = useState<number | null>(null);
    const [hasNavigationMenuBeenOpened, setHasNavigationMenuBeenOpened] = useState(false);
    const [shouldOpenMenuImmediately, setShouldOpenMenuImmediately] = useState(false);
    const [isMoreMenuOpened, setIsMoreMenuOpened] = useState(false);
    const [hasMoreMenuBeenOpened, setHasMoreMenuBeenOpened] = useState(false);
    const shouldFocusNavigationMenuRef = useRef(false);
    const navigationMenuRef = useRef<HTMLDivElement>(null);
    const moreMenuId = `${id}-more-menu`;
    const isMoreMenuOpenedDelayed = useDebounce(
        isMoreMenuOpened,
        !isMoreMenuOpened || hasMoreMenuBeenOpened ? 0 : HOVER_MENU_DELAY,
    );
    const activeNavigationItemKeyDelayed = useDebounce(
        activeNavigationItemKey,
        activeNavigationItemKey === null || hasNavigationMenuBeenOpened || shouldOpenMenuImmediately
            ? 0
            : HOVER_MENU_DELAY,
    );
    const {
        hasOverflowNavigationItems,
        isNavigationMeasured,
        moreNavigationItemRef,
        navigationItemRefs,
        navigationRef,
        overflowNavigationItems,
        shouldRenderMoreNavigationItem,
        visibleNavigationItems,
    } = useNavigationOverflow({ itemClassName, navigation });
    const activeNavigationItem = navigation.find(
        (navigationItem) => getNavigationItemKey(navigationItem) === activeNavigationItemKeyDelayed,
    );
    const activeNavigationItemSkeletonType = activeNavigationItem
        ? getNavigationItemSkeletonType(activeNavigationItem, catalogUrl)
        : undefined;
    const activeNavigationItemMenuId = activeNavigationItem
        ? getNavigationItemMenuId(activeNavigationItem, id)
        : undefined;

    const updateNavigationOverlayTop = useCallback(() => {
        setNavigationOverlayTop(navigationRef.current?.getBoundingClientRect().bottom ?? null);
    }, []);

    useEffect(() => {
        if (!hasOverflowNavigationItems) {
            setIsMoreMenuOpened(false);
        }
    }, [hasOverflowNavigationItems]);

    useEffect(() => {
        if (activeNavigationItemKeyDelayed !== null) {
            setHasNavigationMenuBeenOpened(true);
            setShouldOpenMenuImmediately(false);
            updateNavigationOverlayTop();
        }

        if (isMoreMenuOpenedDelayed) {
            setHasMoreMenuBeenOpened(true);
            updateNavigationOverlayTop();
        }
    }, [activeNavigationItemKeyDelayed, isMoreMenuOpenedDelayed, updateNavigationOverlayTop]);

    useEffect(() => {
        if (!activeNavigationItem || !shouldFocusNavigationMenuRef.current) {
            return;
        }

        shouldFocusNavigationMenuRef.current = false;
        navigationMenuRef.current?.querySelector<HTMLAnchorElement>('a[href]')?.focus();
    }, [activeNavigationItem]);

    useEffect(() => {
        if (activeNavigationItemKeyDelayed === null && !isMoreMenuOpenedDelayed) {
            setNavigationOverlayTop(null);

            return undefined;
        }

        window.addEventListener('resize', updateNavigationOverlayTop);
        window.addEventListener('scroll', updateNavigationOverlayTop, { passive: true });

        return () => {
            window.removeEventListener('resize', updateNavigationOverlayTop);
            window.removeEventListener('scroll', updateNavigationOverlayTop);
        };
    }, [activeNavigationItemKeyDelayed, isMoreMenuOpenedDelayed, updateNavigationOverlayTop]);

    const handleAnimations = () => {
        if (!isFirstHover) {
            setIsFirstHover(true);
            return;
        }
        setIsAnimationDisabled(true);
    };

    const handleEnableAnimation = () => {
        setIsAnimationDisabled(false);
        setIsFirstHover(false);
    };

    const closeNavigationMenu = () => {
        setActiveNavigationItemKey(null);
        setNavigationOverlayTop(null);
        setHasNavigationMenuBeenOpened(false);
        setShouldOpenMenuImmediately(false);
        shouldFocusNavigationMenuRef.current = false;
    };

    const closeMoreMenu = () => {
        setIsMoreMenuOpened(false);
        setHasMoreMenuBeenOpened(false);
    };

    const openNavigationMenu = (navigationItemKey: string, openImmediately = false, focusMenu = false) => {
        closeMoreMenu();
        updateNavigationOverlayTop();
        setShouldOpenMenuImmediately(openImmediately);
        shouldFocusNavigationMenuRef.current = focusMenu;
        setActiveNavigationItemKey(navigationItemKey);
    };

    const openMoreMenu = (shouldDelay = false) => {
        closeNavigationMenu();

        if (!shouldDelay) {
            setHasMoreMenuBeenOpened(true);
        }

        setIsMoreMenuOpened(true);
    };

    const toggleMoreMenu = () => {
        if (isMoreMenuOpenedDelayed) {
            closeMoreMenu();

            return;
        }

        openMoreMenu(false);
    };

    const handleNavigationMouseLeave = () => {
        closeNavigationMenu();
        closeMoreMenu();
        handleEnableAnimation();
    };

    const handleNavigationBlur: FocusEventHandler<HTMLElement> = (event) => {
        if (!(event.relatedTarget instanceof Node) || !event.currentTarget.contains(event.relatedTarget)) {
            closeNavigationMenu();
            closeMoreMenu();
        }
    };

    return (
        /* biome-ignore lint/a11y/noNoninteractiveElementInteractions: The navigation wrapper needs pointer tracking to keep the detached submenu open while preserving its navigation semantics. */
        <nav
            aria-label={t('Main navigation', { ns: 'accessibility' })}
            className="relative"
            id={id}
            tabIndex={-1}
            onBlur={handleNavigationBlur}
            onMouseLeave={handleNavigationMouseLeave}
        >
            <ul
                ref={navigationRef}
                className={twJoin(
                    'vl:flex hidden w-full min-w-0 justify-start overflow-visible',
                    !isNavigationMeasured && 'invisible',
                )}
            >
                {visibleNavigationItems.map((navigationItem, index) => {
                    const navigationItemKey = getNavigationItemKey(navigationItem);

                    return (
                        <NavigationItem
                            key={navigationItemKey}
                            handleAnimations={handleAnimations}
                            isMenuOpened={activeNavigationItemKeyDelayed === navigationItemKey}
                            itemRef={(element) => {
                                navigationItemRefs.current[index] = element;
                            }}
                            itemClassName={itemClassName}
                            menuId={getNavigationItemMenuId(navigationItem, id)}
                            navigationItem={navigationItem}
                            shouldReduceMotion={!!shouldReduceMotion}
                            onMenuClose={closeNavigationMenu}
                            onMenuOpen={() => openNavigationMenu(navigationItemKey)}
                            onMenuOpenAndFocus={() => openNavigationMenu(navigationItemKey, true, true)}
                            onMenuOpenImmediately={() => openNavigationMenu(navigationItemKey, true)}
                        />
                    );
                })}

                {shouldRenderMoreNavigationItem && (
                    <NavigationMoreItem
                        isOpened={isMoreMenuOpenedDelayed}
                        itemRef={moreNavigationItemRef}
                        itemClassName={itemClassName}
                        menuId={moreMenuId}
                        onClose={closeMoreMenu}
                        onOpen={openMoreMenu}
                        onToggle={toggleMoreMenu}
                    />
                )}
            </ul>

            <AnimatePresence initial={false}>
                {(activeNavigationItemKeyDelayed !== null || isMoreMenuOpenedDelayed) &&
                    navigationOverlayTop !== null && (
                        <m.div
                            aria-hidden="true"
                            animate={{ opacity: 1 }}
                            className="pointer-events-none fixed right-0 bottom-0 left-0 z-overlay bg-overlay-default"
                            exit={{ opacity: 0 }}
                            initial={{ opacity: 0 }}
                            style={{ top: navigationOverlayTop }}
                            transition={shouldReduceMotion ? {} : { type: 'tween', duration: 0.15 }}
                        />
                    )}
            </AnimatePresence>

            <AnimatePresence initial={false}>
                {(isNavigationItemWithCategories(activeNavigationItem) || isMoreMenuOpenedDelayed) && (
                    <AnimateNavigationMenu
                        id={isMoreMenuOpenedDelayed ? undefined : activeNavigationItemMenuId}
                        className={twJoin(
                            'absolute right-0 left-0 z-aboveOverlay bg-background-default shadow-md',
                            isMoreMenuOpenedDelayed && 'grid! grid-cols-4 gap-11 px-10',
                        )}
                        disableAnimation={isAnimationDisabled || !!shouldReduceMotion}
                    >
                        {isMoreMenuOpenedDelayed ? (
                            <NavigationMoreMenu
                                id={moreMenuId}
                                navigationId={id}
                                navigationItems={overflowNavigationItems}
                                onLinkClick={closeMoreMenu}
                                onNavigationItemOpen={(navigationItem) =>
                                    openNavigationMenu(getNavigationItemKey(navigationItem), true, true)
                                }
                            />
                        ) : (
                            isNavigationItemWithCategories(activeNavigationItem) && (
                                <div
                                    ref={navigationMenuRef}
                                    className="vl:mx-auto grid w-full vl:max-w-default-max-width gap-8 p-8"
                                >
                                    <NavigationItemColumn
                                        columnCategories={activeNavigationItem.categoriesByColumns}
                                        skeletonType={activeNavigationItemSkeletonType}
                                        onLinkClick={closeNavigationMenu}
                                    />
                                </div>
                            )
                        )}
                    </AnimateNavigationMenu>
                )}
            </AnimatePresence>
        </nav>
    );
};
