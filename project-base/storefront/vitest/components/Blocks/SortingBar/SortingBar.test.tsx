import { fireEvent, render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';
import { pressEnterKey, pressEscapeKey, pressSpaceKey } from 'vitest/utils/accessibility/keyboard-navigation';

const { mockScrollToProductListControls } = vi.hoisted(() => ({
    mockScrollToProductListControls: vi.fn(),
}));
const mockUpdateSortQuery = vi.fn();
const mockCanSeePrices = vi.fn().mockReturnValue(true);
const mockRouterQuery = vi.fn().mockReturnValue({});
const mockRouterLocale = vi.fn().mockReturnValue(undefined);

vi.mock('components/Blocks/Product/Filter/filterElementIds', () => ({
    PRODUCT_LIST_CONTROLS_ELEMENT_ID: 'product-list-controls',
    scrollToProductListControls: mockScrollToProductListControls,
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({
        canSeePrices: mockCanSeePrices(),
    }),
}));

vi.mock('components/Blocks/Product/ProductsList/ProductListViewModeToggle', () => ({
    ProductListViewModeToggle: () => <div data-testid="product-list-view-mode-toggle" />,
}));

vi.mock('utils/queryParams/useUpdateSortQuery', async (importOriginal) => ({
    ...(await importOriginal<typeof import('utils/queryParams/useUpdateSortQuery')>()),
    useUpdateSortQuery: () => mockUpdateSortQuery,
}));

vi.mock('utils/queryParams/useCurrentSortQuery', () => ({
    useCurrentSortQuery: () => null,
}));

vi.mock('config/constants', async (importOriginal) => ({
    ...(await importOriginal<typeof import('config/constants')>()),
    DEFAULT_SORT: 'PRIORITY',
}));

vi.mock('next/router', () => ({
    useRouter: () => ({
        push: vi.fn(),
        pathname: '/products',
        query: mockRouterQuery(),
        asPath: '/products',
        locale: mockRouterLocale(),
    }),
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => {
            const translations: { [key: string]: string } = {
                'products count': 'Products',
            };

            return translations[key] || key;
        },
    }),
}));

const ALL_SORT_OPTIONS = [
    TypeProductOrderingModeEnum.Priority,
    TypeProductOrderingModeEnum.PriceAsc,
    TypeProductOrderingModeEnum.PriceDesc,
    TypeProductOrderingModeEnum.Relevance,
    TypeProductOrderingModeEnum.NameAsc,
    TypeProductOrderingModeEnum.NameDesc,
];

const defaultProps = {
    totalCount: 125,
    sorting: TypeProductOrderingModeEnum.Priority,
    customSortOptions: ALL_SORT_OPTIONS,
};

type RenderSortingBarProps = Omit<Partial<typeof defaultProps>, 'sorting'> & {
    sorting?: TypeProductOrderingModeEnum | null;
};

const renderSortingBar = (props?: RenderSortingBarProps) =>
    render(
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <SortingBar {...defaultProps} {...props} />
        </DomainConfigProvider>,
    );

const getSortButton = () => screen.getByRole('button', { name: /Sort products/ });

const getSortDropdown = () => document.getElementById('sort-dropdown')!;

const isSortDropdownOpen = () => !getSortDropdown().classList.contains('hidden');

const getSortDropdownLinks = () => within(getSortDropdown()).getAllByRole('link');

const getSortDropdownLink = (name: string) => within(getSortDropdown()).getByText(name).closest('a')!;

const getDesktopTabs = () =>
    screen
        .getAllByRole('navigation', { name: 'Sort options' })
        .find((navigation) => navigation.id !== getSortButton().getAttribute('aria-controls'))!;

const queryMoreButton = () => within(getDesktopTabs()).queryByRole('button', { name: 'More' });

const getMoreButton = () => within(getDesktopTabs()).getByRole('button', { name: 'More' });

const getMoreList = () => document.getElementById(getMoreButton().getAttribute('aria-controls')!)!;

// The links listed under "More" are not tabs.
const getDesktopTabLinks = () => {
    const moreList = queryMoreButton() ? getMoreList() : null;

    return within(getDesktopTabs())
        .getAllByRole('link')
        .filter((link) => !moreList?.contains(link));
};

describe('SortingBar', () => {
    beforeEach(() => {
        mockCanSeePrices.mockReturnValue(true);
        mockRouterQuery.mockReturnValue({});
        mockRouterLocale.mockReturnValue(undefined);
        useSessionStore.setState({ originalCategorySlug: undefined });
    });

    describe('Mobile sort button', () => {
        test.each([
            [TypeProductOrderingModeEnum.PriceAsc, 'Sort: Cheapest'],
            [TypeProductOrderingModeEnum.PriceDesc, 'Sort: Most expensive'],
            [TypeProductOrderingModeEnum.Priority, 'Sort: Priority'],
            [null, 'Sort: Priority'],
        ])('shows the short label of the current sort option %s', (sorting, buttonText) => {
            renderSortingBar({ sorting });

            expect(getSortButton()).toHaveTextContent(buttonText);
        });

        test('keeps the full label in the list', async () => {
            const user = userEvent.setup();
            renderSortingBar({ sorting: TypeProductOrderingModeEnum.PriceAsc });

            await user.click(getSortButton());

            expect(getSortDropdownLink('Price ascending')).toHaveAttribute('aria-current', 'true');
        });

        test('exposes the sort list as a disclosure', () => {
            renderSortingBar();

            const sortButton = getSortButton();

            expect(sortButton).toHaveAttribute('aria-controls', 'sort-dropdown');
            expect(sortButton).toHaveAttribute('aria-expanded', 'false');
            expect(sortButton).not.toHaveAttribute('aria-haspopup');
            expect(getSortDropdown().tagName).toBe('NAV');
            expect(screen.queryByRole('menu')).not.toBeInTheDocument();
            expect(isSortDropdownOpen()).toBe(false);
        });

        test('opens on click and moves focus to the active option', async () => {
            const user = userEvent.setup();
            renderSortingBar({ sorting: TypeProductOrderingModeEnum.PriceDesc });

            await user.click(getSortButton());

            expect(isSortDropdownOpen()).toBe(true);
            expect(getSortButton()).toHaveAttribute('aria-expanded', 'true');
            expect(getSortDropdownLinks()).toHaveLength(ALL_SORT_OPTIONS.length);
            expect(getSortDropdownLink('Price descending')).toHaveFocus();
        });

        test.each([
            ['Enter', pressEnterKey],
            ['Space', pressSpaceKey],
        ])('opens with the %s key', async (_key, pressKey) => {
            renderSortingBar();

            getSortButton().focus();
            await pressKey();

            expect(isSortDropdownOpen()).toBe(true);
        });

        test('closes when the button is clicked again', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            await user.click(getSortButton());
            await user.click(getSortButton());

            expect(isSortDropdownOpen()).toBe(false);
        });

        test('closes with Escape and returns focus to the button', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            await user.click(getSortButton());

            expect(getSortDropdownLink('Priority')).toHaveFocus();

            await pressEscapeKey();

            expect(isSortDropdownOpen()).toBe(false);
            expect(getSortButton()).toHaveFocus();
        });

        test('Escape does not move focus while the lists are closed', async () => {
            render(
                <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                    <SortingBar {...defaultProps} />
                    <button type="button">Outside</button>
                </DomainConfigProvider>,
            );

            const outsideButton = screen.getByRole('button', { name: 'Outside' });
            outsideButton.focus();
            await pressEscapeKey();

            expect(outsideButton).toHaveFocus();
        });

        test('returns focus to the button after an option is chosen with the keyboard', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            await user.click(getSortButton());
            getSortDropdownLink('Price ascending').focus();
            await pressEnterKey();

            expect(mockUpdateSortQuery).toHaveBeenCalledWith(TypeProductOrderingModeEnum.PriceAsc);
            expect(isSortDropdownOpen()).toBe(false);
            expect(getSortButton()).toHaveFocus();
        });

        test('closes when the overlay is clicked', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            await user.click(getSortButton());
            await user.click(document.querySelector(`[data-tid="${TIDs.overlay}"]`)!);

            expect(isSortDropdownOpen()).toBe(false);
        });
    });

    describe('Sort options', () => {
        test('marks only the active option with aria-current and keeps real links', async () => {
            const user = userEvent.setup();
            renderSortingBar({ sorting: TypeProductOrderingModeEnum.Priority });

            await user.click(getSortButton());

            getSortDropdownLinks().forEach((link) => {
                expect(link).toHaveAttribute('href', expect.stringMatching(/^\/products/));
                expect(link).not.toHaveAttribute('aria-selected');
            });
            const currentLinks = getSortDropdownLinks().filter((link) => link.getAttribute('aria-current') === 'true');

            expect(currentLinks).toHaveLength(1);
        });

        test('link to the first page and leave out the default sort, like the sort change itself', () => {
            mockRouterQuery.mockReturnValue({
                q: 'tv',
                page: '2',
                lm: '3',
                sort: TypeProductOrderingModeEnum.PriceAsc,
            });
            renderSortingBar({ sorting: TypeProductOrderingModeEnum.PriceAsc });

            const [priorityLink, priceAscendingLink] = getDesktopTabLinks();

            expect(priorityLink).toHaveAttribute('href', '/products?q=tv');
            expect(priceAscendingLink).toHaveAttribute(
                'href',
                `/products?q=tv&sort=${TypeProductOrderingModeEnum.PriceAsc}`,
            );
        });

        test('leave out empty values and the internal slug type, like the sort change itself', () => {
            mockRouterQuery.mockReturnValue({ q: 'tv', utm_source: '', slugType: 'front_product_list' });
            renderSortingBar();

            const [priorityLink, priceAscendingLink] = getDesktopTabLinks();

            expect(priorityLink).toHaveAttribute('href', '/products?q=tv');
            expect(priceAscendingLink).toHaveAttribute(
                'href',
                `/products?q=tv&sort=${TypeProductOrderingModeEnum.PriceAsc}`,
            );
        });

        test('link the default option to the plain URL when no other query is set', () => {
            renderSortingBar();

            expect(getDesktopTabLinks()[0]).toHaveAttribute('href', '/products');
        });

        test('keep the locale prefix of the domain, which the router path leaves out', () => {
            mockRouterLocale.mockReturnValue('sk');
            renderSortingBar();

            const [priorityLink, priceAscendingLink] = getDesktopTabLinks();

            expect(priorityLink).toHaveAttribute('href', '/sk/products');
            expect(priceAscendingLink).toHaveAttribute(
                'href',
                `/sk/products?sort=${TypeProductOrderingModeEnum.PriceAsc}`,
            );
        });

        test('link from an SEO category to its original category, like the sort change itself', () => {
            useSessionStore.setState({ originalCategorySlug: '/electronics' });
            renderSortingBar();

            const [, priceAscendingLink] = getDesktopTabLinks();

            expect(priceAscendingLink).toHaveAttribute(
                'href',
                `/electronics?sort=${TypeProductOrderingModeEnum.PriceAsc}`,
            );
        });

        test('hides price options when the customer cannot see prices', async () => {
            const user = userEvent.setup();
            mockCanSeePrices.mockReturnValue(false);
            renderSortingBar();

            await user.click(getSortButton());

            expect(getSortDropdownLinks()).toHaveLength(4);
            expect(within(getSortDropdown()).queryByText('Price ascending')).not.toBeInTheDocument();
            expect(within(getSortDropdown()).queryByText('Price descending')).not.toBeInTheDocument();
        });

        test('changes the sort, scrolls to the list and closes the dropdown', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            await user.click(getSortButton());
            await user.click(getSortDropdownLink('Price ascending'));

            expect(mockUpdateSortQuery).toHaveBeenCalledWith(TypeProductOrderingModeEnum.PriceAsc);
            expect(mockScrollToProductListControls).toHaveBeenCalledOnce();
            expect(isSortDropdownOpen()).toBe(false);
        });

        test.each(['ctrlKey', 'metaKey', 'shiftKey'])(
            'keeps the browser behaviour for a click with %s',
            async (modifierKey) => {
                const user = userEvent.setup();
                let isDefaultPreventedByComponent: boolean | undefined;
                // jsdom cannot open a new tab, so stop the navigation after the component has handled the click.
                const recordAndStopNavigation = (event: MouseEvent) => {
                    isDefaultPreventedByComponent = event.defaultPrevented;
                    event.preventDefault();
                };

                renderSortingBar();
                await user.click(getSortButton());

                document.addEventListener('click', recordAndStopNavigation);
                fireEvent.click(getSortDropdownLink('Price ascending'), { [modifierKey]: true });
                document.removeEventListener('click', recordAndStopNavigation);

                expect(isDefaultPreventedByComponent).toBe(false);
                expect(mockUpdateSortQuery).not.toHaveBeenCalled();
            },
        );
    });

    describe('Desktop tabs', () => {
        test('show at most four options and the rest under "More"', () => {
            renderSortingBar();

            const tabLinks = getDesktopTabLinks();

            expect(tabLinks.map((link) => link.textContent)).toEqual([
                'Priority',
                'Price ascending',
                'Price descending',
                'Relevance',
            ]);
            expect(getMoreButton()).toBeInTheDocument();
        });

        test('show every option without "More" when there are four or fewer', () => {
            renderSortingBar({
                customSortOptions: [TypeProductOrderingModeEnum.Priority, TypeProductOrderingModeEnum.PriceAsc],
            });

            expect(getDesktopTabLinks()).toHaveLength(2);
            expect(queryMoreButton()).not.toBeInTheDocument();
        });

        test('keep the selected option visible when it would be under "More"', () => {
            renderSortingBar({ sorting: TypeProductOrderingModeEnum.NameDesc });

            const tabLinks = getDesktopTabLinks();

            expect(tabLinks).toHaveLength(4);
            expect(tabLinks[3]).toHaveTextContent('Name descending');
            expect(tabLinks[3]).toHaveAttribute('aria-current', 'true');
        });

        test('"More" opens the remaining options and closes after a selection', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            const moreButton = getMoreButton();
            await user.click(moreButton);

            expect(moreButton).toHaveAttribute('aria-expanded', 'true');

            const moreList = getMoreList();
            expect(
                within(moreList)
                    .getAllByRole('link')
                    .map((link) => link.textContent),
            ).toEqual(['Name ascending', 'Name descending']);

            await user.click(within(moreList).getByText('Name ascending'));

            expect(mockUpdateSortQuery).toHaveBeenCalledWith(TypeProductOrderingModeEnum.NameAsc);
            expect(moreButton).toHaveAttribute('aria-expanded', 'false');
        });

        test('"More" returns focus to its button after an option is chosen with the keyboard', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            const moreButton = getMoreButton();
            await user.click(moreButton);
            within(getMoreList()).getByText('Name ascending').closest('a')!.focus();
            await pressEnterKey();

            expect(mockUpdateSortQuery).toHaveBeenCalledWith(TypeProductOrderingModeEnum.NameAsc);
            expect(moreButton).toHaveAttribute('aria-expanded', 'false');
            expect(moreButton).toHaveFocus();
        });

        test('"More" closes with Escape and returns focus to its button', async () => {
            const user = userEvent.setup();
            renderSortingBar();

            const moreButton = getMoreButton();
            await user.click(moreButton);

            const moreListLink = within(getMoreList()).getByText('Name ascending').closest('a')!;
            moreListLink.focus();
            expect(moreListLink).toHaveFocus();

            await pressEscapeKey();

            expect(moreButton).toHaveAttribute('aria-expanded', 'false');
            expect(moreButton).toHaveFocus();
        });
    });

    describe('Product count and view toggle', () => {
        test('render the product count and the view mode toggle', () => {
            renderSortingBar();

            expect(screen.getByText('125 Products')).toBeInTheDocument();
            expect(screen.getByTestId('product-list-view-mode-toggle')).toBeInTheDocument();
        });

        test('stay when there is a single sort option, the sorting itself is hidden', () => {
            renderSortingBar({ customSortOptions: [TypeProductOrderingModeEnum.Priority] });

            expect(screen.queryByRole('button', { name: /Sort products/ })).not.toBeInTheDocument();
            expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
            expect(screen.getByText('125 Products')).toBeInTheDocument();
            expect(screen.getByTestId('product-list-view-mode-toggle')).toBeInTheDocument();
        });
    });
});
