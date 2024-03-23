import { FooterContact } from './FooterContact';
import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem';
import { SimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';
import { useArticlesQuery } from 'graphql/requests/articlesInterface/articles/queries/ArticlesQuery.generated';
import { ArticlePlacementTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';

export const FooterMenu: FC = () => {
    const { t } = useTranslation();
    const [{ data }] = useArticlesQuery({
        variables: {
            placement: [
                ArticlePlacementTypeEnum.Footer1,
                ArticlePlacementTypeEnum.Footer2,
                ArticlePlacementTypeEnum.Footer3,
                ArticlePlacementTypeEnum.Footer4,
            ],
            first: 100,
        },
    });

    const items = useMemo(
        () => [
            {
                key: 'about-cc',
                title: t('About Shopsys'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnum.Footer1),
            },
            {
                key: 'about-shopping',
                title: t('About shopping'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnum.Footer2),
            },
            {
                key: 'e-shop',
                title: t('E-shop'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnum.Footer3),
            },
            {
                key: 'stores',
                title: t('Stores'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnum.Footer4),
            },
        ],
        [data?.articles.edges],
    );

    return (
        <div className="flex w-full flex-col flex-wrap gap-6 text-center lg:flex-row lg:justify-center lg:text-left vl:flex-nowrap vl:justify-between">
            {items.map((item) => (
                <div key={item.key} className="flex-1">
                    <FooterMenuItem items={item.items} title={item.title} />
                </div>
            ))}

            <div className="flex basis-full flex-col items-center vl:flex-1">
                <FooterContact />
            </div>
        </div>
    );
};

const filterArticlesByPlacement = (
    array: ({ node: SimpleNotBlogArticleFragment | null } | null)[] | undefined | null,
    placement: ArticlePlacementTypeEnum,
): SimpleNotBlogArticleFragment[] =>
    array?.reduce(
        (prev, current) => (current?.node?.placement === placement ? [...prev, current.node] : prev),
        [] as SimpleNotBlogArticleFragment[],
    ) ?? [];
