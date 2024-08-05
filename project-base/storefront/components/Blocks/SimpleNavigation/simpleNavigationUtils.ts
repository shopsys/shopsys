import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropTypeTypename } from 'types/simpleNavigation';

export const getLinkType = (type: ListedItemPropTypeTypename | undefined): PageType | undefined => {
    switch (type) {
        case 'ArticleSite':
            return 'article';
        case 'BlogArticle':
            return 'blogArticle';
        case 'Brand':
            return 'brand';
        case 'Category':
            return 'category';
        default:
            return undefined;
    }
};
