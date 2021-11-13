import { ListedArticleType, ListedBlogArticleType } from './types';
import { ListedArticleFragmentApi } from 'graphql/generated';
import { mapImageApiData } from 'connectors/image/Image';

export const mapListedArticleApiData = (
    apiData: ListedArticleFragmentApi,
): ListedArticleType | ListedBlogArticleType | undefined => {
    if (apiData.__typename === 'Article') {
        return {
            ...apiData,
            image: null,
        };
    }

    if (apiData.__typename === 'BlogArticle') {
        return {
            ...apiData,
            image: mapImageApiData([apiData.image]),
        };
    }

    return undefined;
};
