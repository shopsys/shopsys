import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Navigation } from 'components/Layout/Header/Navigation/Navigation';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import type { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

vi.mock('next/router', () => ({
    useRouter: () => ({
        push: vi.fn(),
        prefetch: vi.fn().mockResolvedValue(undefined),
        pathname: '/',
        query: {},
        asPath: '/',
    }),
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
        lang: 'en',
    }),
}));

vi.mock('components/Basic/Animations/AnimateNavigationMenu', () => ({
    AnimateNavigationMenu: ({ children, className }: { children: ReactNode; className?: string }) => (
        <div className={className}>{children}</div>
    ),
}));

const visibleNavigationItem: TypeCategoriesByColumnFragment = {
    __typename: 'NavigationItem',
    name: 'Catalog',
    type: 'link',
    link: '/catalog',
    routeName: null,
    categoriesByColumns: [],
};

const navigationItemWithChildren: TypeCategoriesByColumnFragment = {
    __typename: 'NavigationItem',
    name: 'Electronics',
    type: 'categories',
    link: null,
    routeName: null,
    categoriesByColumns: [
        {
            __typename: 'NavigationItemCategoriesByColumns',
            columnNumber: 1,
            categories: [
                {
                    __typename: 'Category',
                    uuid: '00000000-0000-0000-0000-000000000001',
                    name: 'Televisions',
                    slug: '/televisions',
                    mainImage: null,
                    children: [],
                },
            ],
        },
    ],
};

const overflowNavigationItem: TypeCategoriesByColumnFragment = {
    __typename: 'NavigationItem',
    name: 'Gift Ideas',
    type: 'link',
    link: '/gift-ideas',
    routeName: null,
    categoriesByColumns: [],
};

vi.mock('components/Layout/Header/Navigation/useNavigationOverflow', () => ({
    useNavigationOverflow: () => ({
        hasOverflowNavigationItems: true,
        isNavigationMeasured: true,
        moreNavigationItemRef: { current: null },
        navigationItemRefs: { current: [] },
        navigationRef: { current: null },
        overflowNavigationItems: [overflowNavigationItem],
        shouldRenderMoreNavigationItem: true,
        visibleNavigationItems: [navigationItemWithChildren, visibleNavigationItem],
    }),
}));

const renderNavigation = () =>
    render(
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <Navigation navigation={[navigationItemWithChildren, visibleNavigationItem, overflowNavigationItem]} />
        </DomainConfigProvider>,
    );

describe('Navigation', () => {
    test('renders a category submenu at full width inside the centered menu container', async () => {
        const user = userEvent.setup();
        renderNavigation();

        await user.tab();

        const categoryLink = await screen.findByRole('link', { name: 'Televisions' });
        const categoryGrid = categoryLink.closest('ul');
        const overlay = document.querySelector('.fixed.bg-overlay-default');

        expect(categoryGrid).toHaveClass('grid-cols-4');
        expect(categoryGrid?.parentElement).toHaveClass('w-full', 'vl:max-w-default-max-width');
        expect(categoryGrid?.parentElement?.parentElement).not.toHaveClass('grid-cols-4');
        expect(overlay).toHaveClass('z-overlay');
    });

    test('closes an opened submenu when keyboard focus moves to an item without children', async () => {
        const user = userEvent.setup();
        renderNavigation();

        await user.tab();

        await waitFor(() => {
            expect(screen.getAllByRole('link', { name: 'Televisions' })[0]).toBeVisible();
        });

        await user.tab();

        expect(screen.getByRole('link', { name: 'Catalog' })).toHaveFocus();
        await waitFor(() => {
            expect(screen.queryAllByRole('link', { name: 'Televisions' })).toHaveLength(0);
        });
    });

    test('keeps More menu open when keyboard focus moves to the first overflow link', async () => {
        const user = userEvent.setup();
        renderNavigation();

        await user.tab();
        await user.tab();
        await user.tab();

        expect(screen.getByRole('button', { name: 'More' })).toHaveFocus();

        await waitFor(() => {
            expect(screen.getByRole('link', { name: 'Gift Ideas' })).toBeVisible();
        });

        await user.tab();

        const overflowLink = screen.getByRole('link', { name: 'Gift Ideas' });

        expect(overflowLink).toBeVisible();
        expect(overflowLink).toHaveFocus();
    });

    test('exposes More as a disclosure and toggles it on repeated clicks', async () => {
        const user = userEvent.setup();
        renderNavigation();

        const moreButton = screen.getByRole('button', { name: 'More' });

        expect(moreButton).toHaveAttribute('aria-controls', 'main-navigation-more-menu');
        expect(moreButton).not.toHaveAttribute('aria-haspopup');

        await user.click(moreButton);

        await waitFor(() => {
            expect(moreButton).toHaveAttribute('aria-expanded', 'true');
            expect(document.getElementById('main-navigation-more-menu')).toBeInTheDocument();
        });

        await user.click(moreButton);

        await waitFor(() => {
            expect(moreButton).toHaveAttribute('aria-expanded', 'false');
            expect(document.getElementById('main-navigation-more-menu')).not.toBeInTheDocument();
        });
    });
});
