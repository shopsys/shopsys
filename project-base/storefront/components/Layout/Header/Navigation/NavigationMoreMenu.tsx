import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { NavigationItemContent } from './NavigationItemContent';
import {
    getNavigationItemKey,
    getNavigationItemMenuId,
    getNavigationItemSkeletonType,
    isNavigationItemLink,
    isNavigationItemWithCategories,
} from './navigationUtils';

type NavigationMoreMenuProps = {
    id: string;
    navigationId: string;
    navigationItems: TypeCategoriesByColumnFragment[];
    onLinkClick: () => void;
    onNavigationItemOpen: (navigationItem: TypeCategoriesByColumnFragment) => void;
};

export const NavigationMoreMenu: FC<NavigationMoreMenuProps> = ({
    id,
    navigationId,
    navigationItems,
    onLinkClick,
    onNavigationItemOpen,
}) => {
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);

    return (
        <ul className="col-span-full grid grid-cols-4 gap-9 py-12" id={id}>
            {navigationItems.map((navigationItem) => {
                const link = navigationItem.link;
                const isLink = isNavigationItemLink(navigationItem);
                const isDropdownTrigger = isNavigationItemWithCategories(navigationItem);
                const isExternalLink = link !== null && (link.startsWith('http://') || link.startsWith('https://'));
                const content = (
                    <NavigationItemContent
                        isDropdownTrigger={isDropdownTrigger}
                        isMenuOpened={false}
                        name={navigationItem.name}
                    />
                );

                return (
                    <li key={getNavigationItemKey(navigationItem)}>
                        {isLink && link !== null ? (
                            <ExtendedNextLink
                                className="mb-2 flex items-center font-bold text-text-default no-underline hover:underline"
                                href={link}
                                rel={isExternalLink ? 'nofollow noreferrer noopener' : undefined}
                                skeletonType={getNavigationItemSkeletonType(navigationItem, catalogUrl)}
                                target={isExternalLink ? '_blank' : undefined}
                                onClick={onLinkClick}
                            >
                                {content}
                            </ExtendedNextLink>
                        ) : (
                            <button
                                aria-controls={
                                    isDropdownTrigger
                                        ? getNavigationItemMenuId(navigationItem, navigationId)
                                        : undefined
                                }
                                aria-expanded={false}
                                aria-haspopup={isDropdownTrigger ? 'true' : undefined}
                                className="mb-2 flex cursor-pointer items-center border-0 bg-transparent p-0 font-bold text-text-default hover:underline disabled:cursor-default"
                                disabled={!isDropdownTrigger}
                                type="button"
                                onClick={() => onNavigationItemOpen(navigationItem)}
                            >
                                {content}
                            </button>
                        )}
                    </li>
                );
            })}
        </ul>
    );
};
