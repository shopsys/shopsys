import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { NavigationItemColumn } from 'components/Layout/Header/Navigation/NavigationItemColumn';
import { AnimatePresence, m } from 'framer-motion';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';
import { useDebounce } from 'utils/useDebounce';

type NavigationItemProps = {
    navigationItem: TypeCategoriesByColumnFragment;
    skeletonType?: PageType;
};

export const NavigationItem: FC<NavigationItemProps> = ({ navigationItem, skeletonType }) => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);
    const hasChildren = !!navigationItem.categoriesByColumns.length;
    const isMenuOpenedDelayed = useDebounce(isMenuOpened, 200);

    return (
        <li className="group" onMouseEnter={() => setIsMenuOpened(true)} onMouseLeave={() => setIsMenuOpened(false)}>
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
                                    ' text-linkInverted',
                                    isMenuOpenedDelayed && 'group-hover:text-linkInvertedHovered',
                                )}
                            />
                        </m.div>
                    )}
                </AnimatePresence>
            </ExtendedNextLink>

            <AnimatePresence initial={false}>
                {hasChildren && isMenuOpenedDelayed && (
                    <AnimateCollapseDiv className="absolute left-0 right-0 z-menu !grid grid-cols-4 gap-11 bg-background px-10 shadow-md">
                        <NavigationItemColumn
                            className="py-12"
                            columnCategories={navigationItem.categoriesByColumns}
                            skeletonType={skeletonType}
                            onLinkClick={() => setIsMenuOpened(false)}
                        />
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </li>
    );
};
