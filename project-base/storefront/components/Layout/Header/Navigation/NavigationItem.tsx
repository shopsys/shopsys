import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { NavigationItemColumn } from 'components/Layout/Header/Navigation/NavigationItemColumn';
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
                    'relative m-0 flex items-center px-6 xl:px-5 py-4 group-first-of-type:pl-0 text-sm font-bold uppercase text-white hover:text-white no-underline hover:no-underline vl:text-base transition-colors',
                    (isMenuOpenedDelayed || !hasChildren) &&
                        'hover:text-secondary group-hover:text-secondary group-hover:no-underline',
                )}
            >
                {navigationItem.name}
                {hasChildren && (
                    <ArrowIcon
                        className={twJoin(
                            'ml-2 text-white',
                            isMenuOpenedDelayed && 'group-hover:rotate-180 group-hover:text-secondary transition-all',
                        )}
                    />
                )}
            </ExtendedNextLink>

            {hasChildren && isMenuOpenedDelayed && (
                <div className="absolute left-0 right-0 z-menu grid grid-cols-4 gap-11 bg-white py-12 px-10 shadow-md">
                    <NavigationItemColumn
                        columnCategories={navigationItem.categoriesByColumns}
                        skeletonType={skeletonType}
                        onLinkClick={() => setIsMenuOpened(false)}
                    />
                </div>
            )}
        </li>
    );
};
