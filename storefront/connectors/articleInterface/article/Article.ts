import { ArticleDetailFragmentApi, SimpleArticleFragmentApi } from 'graphql/generated';
import { ArticleDetailType, SimpleArticleType } from 'types/article';

export const mapArticleDetail = (apiData: ArticleDetailFragmentApi): ArticleDetailType => {
    return {
        ...apiData,
        __typename: 'Article',
    };
};

export const mapSimpleArticle = (apiData: SimpleArticleFragmentApi): SimpleArticleType => {
    return {
        ...apiData,
    };
};
