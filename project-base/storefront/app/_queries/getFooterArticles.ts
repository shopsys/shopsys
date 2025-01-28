import { getArticlesQuery } from './getArticlesQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';
import { TypeArticlePlacementTypeEnum } from 'graphql/types';

export const getFooterArticles = async () => {
    const t = await getTranslation();
    const articlesData = await getArticlesQuery({
        placement: [
            TypeArticlePlacementTypeEnum.Footer1,
            TypeArticlePlacementTypeEnum.Footer2,
            TypeArticlePlacementTypeEnum.Footer3,
            TypeArticlePlacementTypeEnum.Footer4,
        ],
        first: 100,
    });

    const footerArticles = [
        {
            key: 'about-cc',
            title: t('About Shopsys'),
            items: filterArticlesByPlacement(articlesData?.articles.edges, TypeArticlePlacementTypeEnum.Footer1),
        },
        {
            key: 'about-shopping',
            title: t('About shopping'),
            items: filterArticlesByPlacement(articlesData?.articles.edges, TypeArticlePlacementTypeEnum.Footer2),
        },
        {
            key: 'e-shop',
            title: t('E-shop'),
            items: filterArticlesByPlacement(articlesData?.articles.edges, TypeArticlePlacementTypeEnum.Footer3),
        },
        {
            key: 'stores',
            title: t('Stores'),
            items: filterArticlesByPlacement(articlesData?.articles.edges, TypeArticlePlacementTypeEnum.Footer4),
        },
    ];

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
