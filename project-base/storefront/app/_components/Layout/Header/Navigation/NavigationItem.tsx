'use client';

import { NavigationSubItem } from './NavigationSubItem';
import { AnimateNavigationMenu } from 'components/Basic/Animations/AnimateNavigationMenu';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AnimatePresence, m } from 'framer-motion';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';
import { useDebounce } from 'utils/useDebounce';

type NavigationItemProps = {
    navigationItem: TypeCategoriesByColumnFragment;
    skeletonType?: PageType;
    isAnimationDisabled: boolean;
    handleAnimations: () => void;
};

export const NavigationItem: FC<NavigationItemProps> = ({
    navigationItem,
    skeletonType,
    isAnimationDisabled,
    handleAnimations,
}) => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);
    const hasChildren = !!navigationItem.categoriesByColumns.length;
    const isMenuOpenedDelayed = useDebounce(isMenuOpened && true, 200);

    return (
        <li
            className="group"
            onMouseLeave={() => setIsMenuOpened(false)}
            onMouseEnter={() => {
                setIsMenuOpened(true);
                handleAnimations();
            }}
        >
            <ExtendedNextLink
                href={navigationItem.link}
                skeletonType={skeletonType}
                className={twJoin(
                    'relative m-0 flex items-center p-5 font-secondary text-sm font-bold group-first-of-type:pl-0 vl:text-base',
                    'text-linkInverted no-underline',
                    'hover:text-linkInvertedHovered hover:no-underline group-hover:text-linkInvertedHovered group-hover:no-underline',
                    'active:text-linkInvertedHovered',
                    'disabled:text-linkInvertedDisabled',
                )}
            >
                {navigationItem.name}
                <AnimatePresence initial={false}>
                    {hasChildren && (
                        <m.div
                            animate={{ rotate: isMenuOpenedDelayed ? 180 : 0 }}
                            className="ml-2 flex items-start"
                            transition={{ type: 'tween', duration: 0.2 }}
                        >
                            <ArrowIcon
                                className={twJoin(
                                    'size-5 text-linkInverted',
                                    isMenuOpenedDelayed && 'group-hover:text-linkInvertedHovered',
                                )}
                            />
                        </m.div>
                    )}
                </AnimatePresence>
            </ExtendedNextLink>

            <AnimatePresence initial={false}>
                {hasChildren && isMenuOpenedDelayed && (
                    <AnimateNavigationMenu
                        className="absolute left-0 right-0 z-menu !grid grid-cols-4 gap-11 bg-background px-10 shadow-md"
                        disableAnimation={isAnimationDisabled}
                    >
                        <NavigationSubItem
                            className="py-12"
                            columnCategories={navigationItem.categoriesByColumns}
                            skeletonType={skeletonType}
                            onLinkClick={() => setIsMenuOpened(false)}
                        />
                    </AnimateNavigationMenu>
                )}
            </AnimatePresence>
        </li>
    );
};
