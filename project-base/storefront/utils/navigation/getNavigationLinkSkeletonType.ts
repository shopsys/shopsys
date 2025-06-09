import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { FriendlyPagesDestinations, FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';

export function getNavigationLinkSkeletonType(navigationItem: TypeCategoriesByColumnFragment) {
    if (navigationItem.routeName === null) {
        const matchingDestination = Object.entries(FriendlyPagesDestinations).find(([, destination]) => {
            // Remove dynamic parameters from destination path for comparison eg. /categories/[categorySlug] -> /categories/
            const cleanDestination = destination.replace(/\[.*?\]/g, '');

            return navigationItem.link.startsWith(cleanDestination);
        });

        if (matchingDestination) {
            const [pageTypeKey] = matchingDestination;
            const skeletonType = pageTypeKey as FriendlyPagesTypesKey;

            return skeletonType;
        }

        return DEFAULT_SKELETON_TYPE;
    }

    return getPageTypeKey(navigationItem.routeName);
}
