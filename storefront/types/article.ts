export type SimpleArticleSiteType = {
    __typename: 'ArticleSite';
    name: string;
    slug: string;
    external: boolean;
};

export type SimpleArticleLinkType = {
    __typename: 'ArticleLink';
    name: string;
    url: string;
    external: boolean;
};
