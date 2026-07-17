import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { SkeletonEnum } from 'types/skeletons';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { getSkeletonTypeFromLink } from 'utils/skeleton/getSkeletonTypeFromLink';

export const getNavigationItemKey = (navigationItem: TypeCategoriesByColumnFragment) =>
    `${navigationItem.link ?? navigationItem.type}-${navigationItem.name}`;

export const getNavigationItemMenuId = (navigationItem: TypeCategoriesByColumnFragment, navigationId: string) =>
    `${navigationId}-${getNavigationItemKey(navigationItem).replaceAll(/[^a-zA-Z0-9_-]/g, '-')}-menu`;

export const isNavigationItemLink = (
    navigationItem: TypeCategoriesByColumnFragment | undefined,
): navigationItem is TypeCategoriesByColumnFragment => navigationItem?.type === 'link' && navigationItem.link !== null;

export const isNavigationItemWithCategories = (
    navigationItem: TypeCategoriesByColumnFragment | undefined,
): navigationItem is TypeCategoriesByColumnFragment =>
    navigationItem?.type === 'categories' && !!navigationItem.categoriesByColumns.length;

export const getNavigationItemSkeletonType = (
    navigationItem: TypeCategoriesByColumnFragment,
    catalogUrl: string,
): PageType | undefined =>
    navigationItem.link === catalogUrl
        ? SkeletonEnum.Catalog
        : (getPageTypeKey(navigationItem.routeName) ??
          (navigationItem.link !== null ? getSkeletonTypeFromLink(navigationItem.link) : DEFAULT_SKELETON_TYPE));
