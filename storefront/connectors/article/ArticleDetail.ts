import { ArticleDetailFragmentApi } from 'graphql/generated';
import { ArticleDetailType } from './types';

export const mapArticleDetailApiData = (apiData: ArticleDetailFragmentApi): ArticleDetailType => {
    return {
        ...apiData,
        __typename: 'Article',
        text: apiData.text !== undefined ? apiData.text : null,
    };
};
