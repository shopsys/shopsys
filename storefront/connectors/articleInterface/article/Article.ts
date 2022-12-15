import { ArticleDetailFragmentApi, SimpleArticleSiteFragmentApi } from 'graphql/generated';
import { ArticleDetailType, SimpleArticleSiteType } from 'types/article';

export const mapArticleDetail = (apiData: ArticleDetailFragmentApi): ArticleDetailType => {
    return {
        ...apiData,
        __typename: 'ArticleSite',
    };
};

export const mapSimpleArticleSite = (apiData: SimpleArticleSiteFragmentApi): SimpleArticleSiteType => {
    return {
        ...apiData,
    };
};
