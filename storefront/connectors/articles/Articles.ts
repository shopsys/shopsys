import { SimpleArticleFragmentApi, SimpleBlogArticleFragmentApi } from 'graphql/generated';
import { mapImageApiData } from 'connectors/image/Image';
import { SimpleArticleType } from 'types/article';
import { SimpleBlogArticleType } from 'types/blogArticle';

export const mapSimpleArticleApiData = (
    apiData: SimpleArticleFragmentApi | SimpleBlogArticleFragmentApi,
): SimpleArticleType | SimpleBlogArticleType => {
    if (apiData.__typename === 'Article') {
        return {
            ...apiData,
        };
    }

    if (apiData.__typename === 'BlogArticle') {
        return {
            ...apiData,
            image: mapImageApiData([apiData.image]),
        };
    }

    throw new Error('Unknown article typename');
};
