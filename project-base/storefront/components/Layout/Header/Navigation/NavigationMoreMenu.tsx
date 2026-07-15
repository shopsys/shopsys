import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { getNavigationItemKey, getNavigationItemSkeletonType } from './navigationUtils';

type NavigationMoreMenuProps = {
    id: string;
    navigationItems: TypeCategoriesByColumnFragment[];
    onLinkClick: () => void;
};

export const NavigationMoreMenu: FC<NavigationMoreMenuProps> = ({ id, navigationItems, onLinkClick }) => {
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);

    return (
        <ul className="col-span-full grid grid-cols-4 gap-9 py-12" id={id}>
            {navigationItems.map((navigationItem) => (
                <li key={getNavigationItemKey(navigationItem)}>
                    <ExtendedNextLink
                        className="mb-2 block font-bold text-text-default no-underline hover:underline"
                        href={navigationItem.link}
                        skeletonType={getNavigationItemSkeletonType(navigationItem, catalogUrl)}
                        onClick={onLinkClick}
                    >
                        {navigationItem.name}
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    );
};
