import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { NavigationItemColumn } from 'components/Layout/Header/Navigation/NavigationItemColumn';
import { CategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

type NavigationItemProps = {
    navigationItem: CategoriesByColumnFragment;
};

export const NavigationItem: FC<NavigationItemProps> = ({ navigationItem }) => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);
    const hasChildren = !!navigationItem.categoriesByColumns.length;
    const isCatalogLink = navigationItem.link === `/#`;

    return (
        <li className="group" onMouseEnter={() => setIsMenuOpened(true)} onMouseLeave={() => setIsMenuOpened(false)}>
            <ExtendedNextLink
                href={navigationItem.link}
                type={isCatalogLink ? 'homepage' : 'category'}
                className={twJoin(
                    'relative m-0 flex items-center px-2 py-4 text-sm font-bold uppercase text-white no-underline hover:text-orangeLight hover:no-underline group-hover:text-orangeLight group-hover:no-underline vl:text-base',
                )}
            >
                {navigationItem.name}
                {hasChildren && (
                    <ArrowIcon className="ml-2 text-white group-hover:rotate-180 group-hover:text-orangeLight" />
                )}
            </ExtendedNextLink>

            {hasChildren && isMenuOpened && (
                <div className="absolute left-0 right-0 z-menu grid grid-cols-4 gap-11 bg-white py-12 px-10 shadow-md">
                    <NavigationItemColumn
                        columnCategories={navigationItem.categoriesByColumns}
                        onLinkClick={() => setIsMenuOpened(false)}
                    />
                </div>
            )}
        </li>
    );
};
