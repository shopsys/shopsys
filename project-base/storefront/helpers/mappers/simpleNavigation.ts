import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { ListedItemPropType } from 'types/simpleNavigation';

export const getSearchResultLinkType = (listedItem: ListedItemPropType): FriendlyPagesTypesKeys | 'static' => {
    switch (listedItem.__typename) {
        case 'ArticleSite':
            return 'article';
        case 'BlogArticle':
            return 'blogArticle';
        case 'Brand':
            return 'brand';
        case 'Category':
            return 'category';

        default:
            return 'static';
    }
};
