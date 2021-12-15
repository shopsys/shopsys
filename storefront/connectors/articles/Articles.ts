import { ListedArticleFragmentApi } from 'graphql/generated';
import { ListedArticleType } from 'types/article';
import { ListedBlogArticleType } from 'types/blogArticle';
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
