import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { SkeletonEnum } from 'types/skeletons';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { getSkeletonTypeFromLink } from 'utils/skeleton/getSkeletonTypeFromLink';

export const getNavigationItemKey = (navigationItem: TypeCategoriesByColumnFragment) =>
    `${navigationItem.link}-${navigationItem.name}`;

export const getNavigationItemSkeletonType = (
    navigationItem: TypeCategoriesByColumnFragment,
    catalogUrl: string,
): PageType | undefined =>
    navigationItem.link === catalogUrl
        ? SkeletonEnum.Catalog
        : (getPageTypeKey(navigationItem.routeName) ?? getSkeletonTypeFromLink(navigationItem.link));
