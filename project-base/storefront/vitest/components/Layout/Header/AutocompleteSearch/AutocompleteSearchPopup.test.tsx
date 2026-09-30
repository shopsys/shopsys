import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AutocompleteSearchPopup } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearchPopup';
import { type TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { onGtmAutocompleteResultClickEventHandler } from 'gtm/handlers/onGtmAutocompleteResultClickEventHandler';
import type { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

const mockRouterPush = vi.fn();

vi.mock('gtm/handlers/onGtmAutocompleteResultClickEventHandler', () => ({
    onGtmAutocompleteResultClickEventHandler: vi.fn(),
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        href,
        type,
        onClick,
        children,
    }: {
        href: string;
        type: string;
        onClick: () => void;
        children: ReactNode;
    }) => (
        <a
            href={href}
            data-page-type={type}
            onClick={(event) => {
                event.preventDefault();
                onClick();
            }}
        >
            {children}
        </a>
    ),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ push: mockRouterPush }),
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: string[]) => urls,
}));

vi.mock('components/Layout/Header/AutocompleteSearch/AutocompleteSearchBrandsResult', () => ({
    AutocompleteSearchBrandsResult: () => <div>Brand results</div>,
}));

const autocompleteSearchResults = {
    articlesSearch: [],
    brandSearch: [{ __typename: 'Brand', name: 'Apple', slug: '/apple' }],
    categoriesSearch: { __typename: 'CategoryConnection', totalCount: 0, edges: [] },
    productsSearch: {
        __typename: 'ProductConnection',
        edges: [],
        totalCount: 0,
    },
} satisfies TypeAutocompleteSearchQuery;

describe('AutocompleteSearchPopup', () => {
    test('keeps article and blog links and analytics working with only link data', async () => {
        const user = userEvent.setup();
        const onClosePopupCallback = vi.fn();
        const articlesSearch = [
            { __typename: 'ArticleSite', uuid: 'article', name: 'Shopping guide', slug: '/shopping-guide' },
            { __typename: 'BlogArticle', name: 'Shopping tips', slug: '/shopping-tips' },
        ] satisfies TypeAutocompleteSearchQuery['articlesSearch'];

        render(
            <AutocompleteSearchPopup
                areAutocompleteSearchDataFetching={false}
                autocompleteSearchQueryValue="shopping"
                autocompleteSearchResults={{ ...autocompleteSearchResults, articlesSearch }}
                favoritesData={undefined}
                showFavorites={false}
                onClosePopupCallback={onClosePopupCallback}
            />,
        );

        expect(screen.getByText('Articles (2)')).toBeInTheDocument();
        const articleLink = screen.getByRole('link', { name: 'Shopping guide' });
        const blogLink = screen.getByRole('link', { name: 'Shopping tips' });
        expect(articleLink).toHaveAttribute('href', '/shopping-guide');
        expect(articleLink).toHaveAttribute('data-page-type', 'article');
        expect(blogLink).toHaveAttribute('href', '/shopping-tips');
        expect(blogLink).toHaveAttribute('data-page-type', 'blogArticle');

        await user.click(articleLink);
        await user.click(blogLink);

        expect(onClosePopupCallback).toHaveBeenCalledTimes(2);
        expect(onGtmAutocompleteResultClickEventHandler).toHaveBeenNthCalledWith(
            1,
            'shopping',
            GtmSectionType.article,
            'Shopping guide',
        );
        expect(onGtmAutocompleteResultClickEventHandler).toHaveBeenNthCalledWith(
            2,
            'shopping',
            GtmSectionType.article,
            'Shopping tips',
        );
    });

    test('closes the mobile search overlay when navigating to all results', async () => {
        const user = userEvent.setup();
        const onClosePopupCallback = vi.fn();
        const onSearchSubmit = vi.fn();

        render(
            <AutocompleteSearchPopup
                areAutocompleteSearchDataFetching={false}
                autocompleteSearchQueryValue="apple"
                autocompleteSearchResults={autocompleteSearchResults}
                favoritesData={undefined}
                showFavorites={false}
                onClosePopupCallback={onClosePopupCallback}
                onSearchSubmit={onSearchSubmit}
            />,
        );

        await user.click(screen.getByRole('button', { name: 'View all results' }));

        expect(onClosePopupCallback).toHaveBeenCalledOnce();
        expect(onSearchSubmit).toHaveBeenCalledOnce();
        expect(mockRouterPush).toHaveBeenCalledWith({ pathname: '/search', query: { q: 'apple' } });
    });
});
