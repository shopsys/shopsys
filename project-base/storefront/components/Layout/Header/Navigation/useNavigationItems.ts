import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useMemo } from 'react';

type UseNavigationItemsProps = {
    navigation: TypeCategoriesByColumnFragment[];
    visibleItemsCount?: number;
};

export const useNavigationItems = ({ navigation, visibleItemsCount = navigation.length }: UseNavigationItemsProps) => {
    return useMemo(() => {
        const boundedVisibleItemsCount = Math.min(Math.max(visibleItemsCount, 0), navigation.length);
        const visibleNavigationItems = navigation.slice(0, boundedVisibleItemsCount);
        const overflowNavigationItems = navigation.slice(boundedVisibleItemsCount);

        return {
            hasOverflowNavigationItems: overflowNavigationItems.length > 0,
            overflowNavigationItems,
            visibleNavigationItems,
        };
    }, [navigation, visibleItemsCount]);
};
