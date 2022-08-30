import { FooterMenuStyled } from './FooterMenu.style';
import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem/FooterMenuItem';
import { ArticlePlacementTypeEnumApi, SimpleArticleFragmentApi, useArticlesQueryApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';

const TEST_IDENTIFIER = 'layout-footer-footermenu';

export const FooterMenu: FC = () => {
    const t = useTypedTranslationFunction();
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
                title: t('About Commerce Cloud'),
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
        [data?.articles.edges, t],
    );

    return (
        <FooterMenuStyled data-testid={TEST_IDENTIFIER}>
            {items.map((item) => (
                <FooterMenuItem key={item.key} title={item.title} items={item.items} />
            ))}
        </FooterMenuStyled>
    );
};

const filterArticlesByPlacement = (
    array: ({ node: SimpleArticleFragmentApi | null } | null)[] | undefined | null,
    placement: ArticlePlacementTypeEnumApi,
): SimpleArticleFragmentApi[] =>
    array?.reduce(
        (prev, current) => (current?.node?.placement === placement ? [...prev, current.node] : prev),
        [] as SimpleArticleFragmentApi[],
    ) ?? [];
