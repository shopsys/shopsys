import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropTypeTypename } from 'types/simpleNavigation';

export const getLinkType = (type: ListedItemPropTypeTypename | undefined): PageType => {
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
            throw new Error('Link type ' + type + ' could not be resolved.');
    }
};
