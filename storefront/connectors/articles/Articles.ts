import { BlogArticleImageListFragmentApi, ListedArticleFragmentApi } from 'graphql/generated';
import { ListedArticleType, ListedBlogArticleType } from './types';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';

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
            image: mapArticlesImageApiData(apiData.image),
        };
    }

    return undefined;
};

const mapArticlesImageApiData = (apiData: BlogArticleImageListFragmentApi['image']): ImageType | null => {
    if (apiData === null || apiData === undefined || !(0 in apiData)) {
        return null;
    }

    return mapImageSizeApiData(apiData.sizes[0]);
};
