import { FooterContact } from './FooterContact';
import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem';
import { ArticlePlacementTypeEnumApi, SimpleNotBlogArticleFragmentApi, useArticlesQueryApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';

export const FooterMenu: FC = () => {
    const { t } = useTranslation();
    const [{ data }] = useArticlesQueryApi({
        variables: {
            placement: [
                ArticlePlacementTypeEnumApi.Footer1Api,
                ArticlePlacementTypeEnumApi.Footer2Api,
                ArticlePlacementTypeEnumApi.Footer3Api,
                ArticlePlacementTypeEnumApi.Footer4Api,
            ],
            first: 100,
        },
    });

    const items = useMemo(
        () => [
            {
                key: 'about-cc',
                title: t('About Shopsys'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnumApi.Footer1Api),
            },
            {
                key: 'about-shopping',
                title: t('About shopping'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnumApi.Footer2Api),
            },
            {
                key: 'e-shop',
                title: t('E-shop'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnumApi.Footer3Api),
            },
            {
                key: 'stores',
                title: t('Stores'),
                items: filterArticlesByPlacement(data?.articles.edges, ArticlePlacementTypeEnumApi.Footer4Api),
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
    array: ({ node: SimpleNotBlogArticleFragmentApi | null } | null)[] | undefined | null,
    placement: ArticlePlacementTypeEnumApi,
): SimpleNotBlogArticleFragmentApi[] =>
    array?.reduce(
        (prev, current) => (current?.node?.placement === placement ? [...prev, current.node] : prev),
        [] as SimpleNotBlogArticleFragmentApi[],
    ) ?? [];
