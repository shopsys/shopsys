import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';
import { useArticlesQuery } from 'graphql/requests/articlesInterface/articles/queries/ArticlesQuery.generated';
import { TypeArticlePlacementTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FooterArticle } from 'types/footerArticle';

export const useFooterArticles = () => {
    const { t } = useTranslation();
    const [{ data }] = useArticlesQuery({
        variables: {
            placement: [
                TypeArticlePlacementTypeEnum.Footer1,
                TypeArticlePlacementTypeEnum.Footer2,
                TypeArticlePlacementTypeEnum.Footer3,
                TypeArticlePlacementTypeEnum.Footer4,
            ],
            first: 100,
        },
    });

    const footerArticles: FooterArticle[] = useMemo(
        () => [
            {
                key: 'about-cc',
                title: t('About Shopsys'),
                items: filterArticlesByPlacement(data?.articles.edges, TypeArticlePlacementTypeEnum.Footer1),
            },
            {
                key: 'about-shopping',
                title: t('About shopping'),
                items: filterArticlesByPlacement(data?.articles.edges, TypeArticlePlacementTypeEnum.Footer2),
            },
            {
                key: 'e-shop',
                title: t('E-shop'),
                items: filterArticlesByPlacement(data?.articles.edges, TypeArticlePlacementTypeEnum.Footer3),
            },
            {
                key: 'stores',
                title: t('Stores'),
                items: filterArticlesByPlacement(data?.articles.edges, TypeArticlePlacementTypeEnum.Footer4),
            },
        ],
        [data?.articles.edges],
    );

    return footerArticles;
};

const filterArticlesByPlacement = (
    array: ({ node: TypeSimpleNotBlogArticleFragment | null } | null)[] | undefined | null,
    placement: TypeArticlePlacementTypeEnum,
): TypeSimpleNotBlogArticleFragment[] =>
    array?.reduce(
        (prev, current) => (current?.node?.placement === placement ? [...prev, current.node] : prev),
        [] as TypeSimpleNotBlogArticleFragment[],
    ) ?? [];
