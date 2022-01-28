import { mapSimpleArticle } from './article/Article';
import { mapSimpleBlogArticle } from './blogArticle/BlogArticle';
import { SimpleArticleInterfaceFragmentApi } from 'graphql/generated';
import { SimpleArticleInterfaceType } from 'types/articleInterface';

const mapSimpleArticleInterface = (
    apiData: SimpleArticleInterfaceFragmentApi,
): SimpleArticleInterfaceType | undefined => {
    switch (apiData.__typename) {
        case 'Article':
            return mapSimpleArticle(apiData);
        case 'BlogArticle':
            return mapSimpleBlogArticle(apiData);
        default:
            return undefined;
    }
};

export const mapSimpleArticlesInterface = (
    apiData: SimpleArticleInterfaceFragmentApi[],
): SimpleArticleInterfaceType[] => {
    const mappedArticles = [];

    for (const article of apiData) {
        const mappedArticle = mapSimpleArticleInterface(article);
        if (mappedArticle !== undefined) {
            mappedArticles.push(mappedArticle);
        }
    }

    return mappedArticles;
};
