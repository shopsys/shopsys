import { AnimateNavigationMenu } from 'components/Basic/Animations/AnimateNavigationMenu';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { NavigationItemColumn } from 'components/Layout/Header/Navigation/NavigationItemColumn';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { AnimatePresence, m, useReducedMotion } from 'framer-motion';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getNavigationLinkSkeletonType } from 'utils/navigation/getNavigationLinkSkeletonType';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { useDebounce } from 'utils/useDebounce';

type NavigationItemProps = {
    navigationItem: TypeCategoriesByColumnFragment;
    isAnimationDisabled: boolean;
    handleAnimations: () => void;
};

export const NavigationItem: FC<NavigationItemProps> = ({ navigationItem, isAnimationDisabled, handleAnimations }) => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);
    const hasChildren = !!navigationItem.categoriesByColumns.length;
    const isMenuOpenedDelayed = useDebounce(isMenuOpened && true, 200);
    const skeletonType = getNavigationLinkSkeletonType(navigationItem);
    const shouldReduceMotion = useReducedMotion();

    return (
        <li
            className="group"
            onMouseLeave={() => setIsMenuOpened(false)}
            onMouseEnter={() => {
                setIsMenuOpened(true);
                if (!shouldReduceMotion) {
                    handleAnimations();
                }
            }}
        >
            <ExtendedNextLink
                href={navigationItem.link}
                skeletonType={skeletonType}
                className={twJoin(
                    'font-secondary vl:text-base relative m-0 flex items-center p-5 text-sm font-bold whitespace-nowrap group-first-of-type:pl-0',
                    'text-link-inverted-default no-underline',
                    'hover:text-link-inverted-hovered group-hover:text-link-inverted-hovered group-hover:no-underline hover:no-underline',
                    'active:text-link-inverted-hovered',
                    'disabled:text-link-inverted-disabled',
                )}
            >
                {navigationItem.name}
                <AnimatePresence initial={false}>
                    {hasChildren && (
                        <m.div
                            animate={{ rotate: isMenuOpenedDelayed ? 180 : 0 }}
                            className="ml-2 flex items-start"
                            transition={shouldReduceMotion ? {} : { type: 'tween', duration: 0.2 }}
                        >
                            <ArrowIcon
                                className={twJoin(
                                    'text-link-inverted-default size-5',
                                    isMenuOpenedDelayed && 'group-hover:text-link-inverted-hovered',
                                )}
                            />
                        </m.div>
                    )}
                </AnimatePresence>
            </ExtendedNextLink>

            <AnimatePresence initial={false}>
                {hasChildren && isMenuOpenedDelayed && (
                    <AnimateNavigationMenu
                        className="z-menu bg-background-default absolute right-0 left-0 !grid grid-cols-4 gap-11 px-10 shadow-md"
                        disableAnimation={isAnimationDisabled || !!shouldReduceMotion}
                    >
                        <NavigationItemColumn
                            className="py-12"
                            columnCategories={navigationItem.categoriesByColumns}
                            skeletonType={getPageTypeKey(navigationItem.routeName) || DEFAULT_SKELETON_TYPE}
                            onLinkClick={() => setIsMenuOpened(false)}
                        />
                    </AnimateNavigationMenu>
                )}
            </AnimatePresence>
        </li>
    );
};
