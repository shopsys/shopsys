import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';

export type FooterArticle = {
    key: string;
    title: string;
    items: TypeSimpleNotBlogArticleFragment[];
};
