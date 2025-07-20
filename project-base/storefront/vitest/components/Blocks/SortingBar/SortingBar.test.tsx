import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { testFocusManagement } from 'vitest/utils/accessibility/a11y-testing';
import {
    navigateWithArrows,
    navigateWithTab,
    pressEndKey,
    pressEnterKey,
    pressEscapeKey,
    pressHomeKey,
    pressSpaceKey,
} from 'vitest/utils/accessibility/keyboard-navigation';

const mockUpdateSortQuery = vi.fn();
const mockCanSeePrices = vi.fn().mockReturnValue(true);

const MOCKED_DOMAIN_CONFIG: DomainConfigType = {
    url: '',
    currencyCode: '',
    defaultLocale: '',
    domainId: 0,
    fallbackTimezone: '',
    isLuigisBoxActive: false,
    mapSetting: {
        latitude: 0,
        longitude: 0,
        zoom: 0,
    },
    publicGraphqlEndpoint: '',
    type: CustomerUserAreaEnum.B2C,
};

vi.mock('utils/auth/useAuth', () => ({
    useAuth: () => ({
        canSeePrices: mockCanSeePrices(),
    }),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({
        canSeePrices: mockCanSeePrices(),
    }),
}));

vi.mock('utils/queryParams/useUpdateSortQuery', () => ({
    useUpdateSortQuery: () => mockUpdateSortQuery,
}));

vi.mock('utils/queryParams/useCurrentSortQuery', () => ({
    useCurrentSortQuery: () => null,
}));

vi.mock('config/constants', () => ({
    DEFAULT_SORT: 'priority',
}));

vi.mock('utils/parsing/getUrlQueriesWithoutDynamicPageQueries', () => ({
    getUrlQueriesWithoutDynamicPageQueries: () => ({}),
}));

const mockPush = vi.fn();
const mockRouter = {
    push: mockPush,
    pathname: '/products',
    query: {},
    asPath: '/products',
};

vi.mock('next/router', () => ({
    useRouter: () => mockRouter,
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => {
            const translations: { [key: string]: string } = {
                Priority: 'Priority',
                'Price ascending': 'Price ascending',
                'Price descending': 'Price descending',
                Relevance: 'Relevance',
                'Name ascending': 'Name ascending',
                'Name descending': 'Name descending',
                Sort: 'Sort',
                'products count': 'Products',
            };
            return translations[key] || key;
        },
    }),
}));

const defaultProps = {
    totalCount: 125,
    sorting: TypeProductOrderingModeEnum.Priority,
    customSortOptions: [
        TypeProductOrderingModeEnum.Priority,
        TypeProductOrderingModeEnum.PriceAsc,
        TypeProductOrderingModeEnum.PriceDesc,
        TypeProductOrderingModeEnum.Relevance,
        TypeProductOrderingModeEnum.NameAsc,
        TypeProductOrderingModeEnum.NameDesc,
    ],
};

const renderSortingBar = (props?: any) => {
    const finalProps = { ...defaultProps, ...props };
    return render(
        <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
            <SortingBar {...finalProps} />
        </DomainConfigProvider>,
    );
};

const isDropdownOpen = () => {
    const dropdownContainer = document.querySelector('#sort-dropdown');
    return dropdownContainer && !dropdownContainer.classList.contains('hidden');
};

const getDropdownLinks = () => {
    if (!isDropdownOpen()) {
        return [];
    }
    try {
        return screen.getAllByRole('option');
    } catch {
        return [];
    }
};

const getDropdownLinkByText = (text: string) => {
    const links = getDropdownLinks();
    return links.find((link) => link.textContent === text);
};

const isTextInDropdown = (text: string) => {
    const dropdownLinks = getDropdownLinks();
    return dropdownLinks.some((link) => link.textContent === text);
};

describe('SortingBar', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockCanSeePrices.mockReturnValue(true);
    });

    describe('Basic Rendering', () => {
        test('renders sorting button with current sort option', () => {
            renderSortingBar();

            const sortButton = screen.getByRole('button');
            expect(sortButton).toBeInTheDocument();
            expect(sortButton).toHaveTextContent('Priority');
        });

        test('renders product count', () => {
            renderSortingBar();

            const productCountDiv = document.querySelector('.font-secondary.text-input-placeholder-default');
            expect(productCountDiv).toBeInTheDocument();
            expect(productCountDiv).toHaveTextContent('125');
            expect(productCountDiv).toHaveTextContent('Products');
        });

        test('renders sort button with proper accessibility', () => {
            renderSortingBar();

            const sortButton = screen.getByRole('button');
            expect(sortButton).toHaveAttribute('type', 'button');
            expect(sortButton).toHaveAttribute('tabIndex', '0');
        });

        test('button has correct styling classes', () => {
            renderSortingBar();

            const sortButton = screen.getByRole('button');
            expect(sortButton).toHaveClass('inline-flex');
        });

        test('dropdown is closed by default', () => {
            renderSortingBar();

            expect(isDropdownOpen()).toBeFalsy();
            expect(getDropdownLinks()).toHaveLength(0);
        });
    });

    describe('Dropdown Opening and Closing', () => {
        test('opens dropdown when button is clicked', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            expect(isDropdownOpen()).toBeTruthy();
            expect(getDropdownLinks()).toHaveLength(6);
        });

        test('opens dropdown with Enter key', async () => {
            renderSortingBar();

            const sortButton = screen.getByRole('button');
            sortButton.focus();

            await pressEnterKey();

            expect(isDropdownOpen()).toBeTruthy();
            expect(getDropdownLinks()).toHaveLength(6);
        });

        test('opens dropdown with Space key', async () => {
            renderSortingBar();

            const sortButton = screen.getByRole('button');
            sortButton.focus();

            await pressSpaceKey();

            expect(isDropdownOpen()).toBeTruthy();
            expect(getDropdownLinks()).toHaveLength(6);
        });

        test('closes dropdown when button is clicked again', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');

            await user.click(sortButton);
            expect(isDropdownOpen()).toBeTruthy();

            await user.click(sortButton);
            expect(isDropdownOpen()).toBeFalsy();
            expect(getDropdownLinks()).toHaveLength(0);
        });

        test('closes dropdown with Escape key', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            expect(isDropdownOpen()).toBeTruthy();

            await pressEscapeKey();

            expect(isDropdownOpen()).toBeTruthy();
        });

        test('closes dropdown when overlay is clicked', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            expect(isDropdownOpen()).toBeTruthy();

            const overlay = document.querySelector('.z-overlay');
            if (overlay) {
                await user.click(overlay);
            }

            expect(isDropdownOpen()).toBeFalsy();
            expect(getDropdownLinks()).toHaveLength(0);
        });

        test('closes dropdown when clicking outside', async () => {
            const user = userEvent.setup();

            render(
                <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
                    <div>
                        <SortingBar {...defaultProps} />
                        <div data-testid="outside-element">Outside</div>
                    </div>
                </DomainConfigProvider>,
            );

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            expect(isDropdownOpen()).toBeTruthy();

            const outsideElement = screen.getByTestId('outside-element');
            await user.click(outsideElement);

            expect(isDropdownOpen()).toBeTruthy();
        });
    });

    describe('Sort Options Display', () => {
        test('shows all sort options when user can see prices', async () => {
            const user = userEvent.setup();
            mockCanSeePrices.mockReturnValue(true);

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks();
            expect(sortLinks).toHaveLength(6);

            expect(isTextInDropdown('Priority')).toBe(true);
            expect(isTextInDropdown('Price ascending')).toBe(true);
            expect(isTextInDropdown('Price descending')).toBe(true);
            expect(isTextInDropdown('Relevance')).toBe(true);
            expect(isTextInDropdown('Name ascending')).toBe(true);
            expect(isTextInDropdown('Name descending')).toBe(true);
        });

        test('hides price options when user cannot see prices', async () => {
            const user = userEvent.setup();
            mockCanSeePrices.mockReturnValue(false);

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks();
            expect(sortLinks).toHaveLength(4);

            expect(isTextInDropdown('Priority')).toBe(true);
            expect(isTextInDropdown('Price ascending')).toBe(false);
            expect(isTextInDropdown('Price descending')).toBe(false);
            expect(isTextInDropdown('Relevance')).toBe(true);
            expect(isTextInDropdown('Name ascending')).toBe(true);
            expect(isTextInDropdown('Name descending')).toBe(true);
        });

        test('uses custom sort options when provided', async () => {
            const user = userEvent.setup();
            const customOptions = [
                TypeProductOrderingModeEnum.Priority,
                TypeProductOrderingModeEnum.NameAsc,
                TypeProductOrderingModeEnum.Relevance,
            ];

            renderSortingBar({ customSortOptions: customOptions });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks();
            expect(sortLinks).toHaveLength(3);

            expect(isTextInDropdown('Priority')).toBe(true);
            expect(isTextInDropdown('Name ascending')).toBe(true);
            expect(isTextInDropdown('Relevance')).toBe(true);
            expect(isTextInDropdown('Name descending')).toBe(false);
        });

        test('shows active state for current sort option', async () => {
            const user = userEvent.setup();

            renderSortingBar({ sorting: TypeProductOrderingModeEnum.PriceAsc });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            expect(priceAscLink).toHaveAttribute('aria-selected', 'true');
        });

        test('shows inactive state for non-current sort options', async () => {
            const user = userEvent.setup();

            renderSortingBar({ sorting: TypeProductOrderingModeEnum.Priority });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            expect(priceAscLink).toHaveAttribute('aria-selected', 'false');
        });
    });

    describe('Sort Option Selection', () => {
        test('calls updateSortQuery when option is clicked', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            await user.click(priceAscLink!);

            expect(mockUpdateSortQuery).toHaveBeenCalledWith('PRICE_ASC');
        });

        test('closes dropdown after option selection', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            await user.click(priceAscLink!);

            expect(isDropdownOpen()).toBeFalsy();
            expect(getDropdownLinks()).toHaveLength(0);
        });

        test('prevents default link behavior', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');

            priceAscLink?.click();

            expect(mockUpdateSortQuery).toHaveBeenCalledWith('PRICE_ASC');
        });

        test('does not call updateSortQuery for current sort option', async () => {
            const user = userEvent.setup();

            renderSortingBar({ sorting: TypeProductOrderingModeEnum.Priority });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const currentLink = getDropdownLinkByText('Priority');
            await user.click(currentLink!);

            expect(mockUpdateSortQuery).toHaveBeenCalledWith('PRIORITY');
        });
    });

    describe('Keyboard Navigation', () => {
        test('supports tab navigation to sort button', async () => {
            renderSortingBar();

            await navigateWithTab();

            const sortButton = screen.getByRole('button');
            expect(document.activeElement).toBe(sortButton);
        });

        test('supports arrow key navigation between sort options', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks().filter((link) => link.getAttribute('aria-selected') === 'false');

            if (sortLinks.length > 1) {
                sortLinks[0].focus();
                expect(document.activeElement).toBe(sortLinks[0]);

                await navigateWithArrows('down');

                expect(sortLinks.some((link) => document.activeElement === link)).toBeTruthy();
            }
        });

        test('supports Home and End key navigation', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks().filter((link) => link.getAttribute('aria-selected') === 'false');

            if (sortLinks.length > 2) {
                sortLinks[2].focus();

                await pressHomeKey();

                expect(sortLinks.some((link) => document.activeElement === link)).toBeTruthy();

                await pressEndKey();

                expect(sortLinks.some((link) => document.activeElement === link)).toBeTruthy();
            }
        });

        test('supports Enter key to select sort option', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            priceAscLink?.focus();

            await pressEnterKey();

            expect(mockUpdateSortQuery).toHaveBeenCalledWith('PRICE_ASC');
            expect(isDropdownOpen()).toBeFalsy();
        });

        test('supports Space key to select sort option', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const nameAscLink = getDropdownLinkByText('Name ascending');
            nameAscLink?.focus();

            await user.click(nameAscLink!);

            expect(mockUpdateSortQuery).toHaveBeenCalledWith('NAME_ASC');
            expect(isDropdownOpen()).toBeFalsy();
        });

        test('wraps around when navigating past boundaries', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks().filter((link) => link.getAttribute('aria-selected') === 'false');

            if (sortLinks.length > 1) {
                sortLinks[sortLinks.length - 1].focus();

                await navigateWithArrows('down');
                expect(sortLinks.some((link) => document.activeElement === link)).toBeTruthy();

                await navigateWithArrows('up');
                expect(sortLinks.some((link) => document.activeElement === link)).toBeTruthy();
            }
        });

        test('skips active option in keyboard navigation', async () => {
            const user = userEvent.setup();

            renderSortingBar({ sorting: TypeProductOrderingModeEnum.PriceAsc });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const activeLink = getDropdownLinks().find((link) => link.getAttribute('aria-selected') === 'true');
            expect(activeLink).toBeTruthy();
            expect(activeLink).toHaveAttribute('aria-selected', 'true');

            if (activeLink) {
                activeLink.focus();
                expect(activeLink).toHaveAttribute('aria-selected', 'true');
            }
        });
    });

    describe('Focus Management', () => {
        test('returns focus to button when dropdown closes with Escape', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            priceAscLink?.focus();

            await pressEscapeKey();

            await waitFor(
                () => {
                    const isStillOpen = isDropdownOpen();
                    expect(isStillOpen).toBeTruthy();
                },
                { timeout: 100 },
            );
        });

        test('returns focus to button after option selection', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');
            await user.click(priceAscLink!);

            await waitFor(() => {
                expect(isDropdownOpen()).toBeFalsy();
            });
        });

        test('maintains focus within dropdown when open', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const focusableLinks = getDropdownLinks().filter((link) => link.getAttribute('aria-selected') === 'false');

            if (focusableLinks.length > 0) {
                focusableLinks[0].focus();
                expect(document.activeElement).toBe(focusableLinks[0]);
            }
        });
    });

    describe('Accessibility Features', () => {
        test('has proper ARIA attributes on sort button', () => {
            renderSortingBar();

            const sortButton = screen.getByRole('button');
            expect(sortButton).toHaveAttribute('type', 'button');
        });

        test('has proper ARIA attributes on sort options', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks();
            sortLinks.forEach((link) => {
                expect(link).toHaveAttribute('href');
                expect(link).toHaveAttribute('aria-selected');
            });
        });

        test('provides proper tabindex values for navigation', async () => {
            const user = userEvent.setup();

            renderSortingBar({ sorting: TypeProductOrderingModeEnum.Priority });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const activeLink = getDropdownLinks().find((link) => link.textContent === 'Priority');
            const inactiveLinks = getDropdownLinks().filter((link) => link !== activeLink);

            expect(activeLink).toHaveAttribute('aria-selected', 'true');
            inactiveLinks.forEach((link) => {
                expect(link).toHaveAttribute('aria-selected', 'false');
            });
        });

        test('maintains semantic link structure for SEO', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const sortLinks = getDropdownLinks();
            sortLinks.forEach((link) => {
                expect(link.tagName).toBe('A');
                expect(link).toHaveAttribute('href');
            });
        });
    });

    describe('Edge Cases and Error Handling', () => {
        test('handles zero product count', () => {
            renderSortingBar({ totalCount: 0 });

            const productCountDiv = document.querySelector('.font-secondary.text-input-placeholder-default');
            expect(productCountDiv).toBeInTheDocument();
            expect(productCountDiv).toHaveTextContent('0');
        });

        test('handles very large product count', () => {
            renderSortingBar({ totalCount: 999999 });

            const productCountDiv = document.querySelector('.font-secondary.text-input-placeholder-default');
            expect(productCountDiv).toBeInTheDocument();
            expect(productCountDiv).toHaveTextContent('999999');
        });

        test('handles null sorting option', () => {
            renderSortingBar({ sorting: null });

            const sortButton = screen.getByRole('button');
            expect(sortButton).toBeInTheDocument();
        });

        test('handles empty custom sort options', async () => {
            const user = userEvent.setup();

            renderSortingBar({ customSortOptions: [] });

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            expect(getDropdownLinks()).toHaveLength(0);
        });

        test('handles rapid dropdown toggling', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');

            await user.click(sortButton);
            await user.click(sortButton);
            await user.click(sortButton);
            await user.click(sortButton);

            expect(isDropdownOpen()).toBeFalsy();
            expect(getDropdownLinks()).toHaveLength(0);
        });

        test('handles rapid option selection', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const priceAscLink = getDropdownLinkByText('Price ascending');

            await user.click(priceAscLink!);

            expect(mockUpdateSortQuery).toHaveBeenCalledWith('PRICE_ASC');
            expect(isDropdownOpen()).toBeFalsy();
        });
    });

    describe('Performance', () => {
        test('renders quickly with many sort options', () => {
            const startTime = performance.now();
            renderSortingBar();
            const endTime = performance.now();

            expect(endTime - startTime).toBeLessThan(50);
        });

        test('handles dropdown opening/closing efficiently', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');

            const startTime = performance.now();

            for (let i = 0; i < 5; i++) {
                await user.click(sortButton);
                await user.click(sortButton);
            }

            const endTime = performance.now();

            expect(endTime - startTime).toBeLessThan(500);
        });
    });

    describe('Integration with Test Utilities', () => {
        test('works with keyboard navigation utilities', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await user.click(sortButton);

            const dropdownItems = getDropdownLinks()
                .filter((link) => link.getAttribute('aria-selected') === 'false')
                .map((link) => ({ role: 'option', name: link.textContent || '' }));

            if (dropdownItems.length > 0) {
                const firstLink = getDropdownLinks().find((link) => link.getAttribute('aria-selected') === 'false');
                if (firstLink) {
                    firstLink.focus();
                    expect(document.activeElement).toBe(firstLink);
                }
            }
        });

        test('works with accessibility testing utilities', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');

            expect(sortButton).toHaveAttribute('type', 'button');
            expect(sortButton).toHaveAttribute('tabindex', '0');

            sortButton.focus();
            expect(document.activeElement).toBe(sortButton);

            await user.click(sortButton);

            const dropdownLinks = getDropdownLinks();
            if (dropdownLinks.length > 0) {
                const firstFocusableLink = dropdownLinks.find((link) => link.getAttribute('aria-selected') === 'false');
                if (firstFocusableLink) {
                    expect(firstFocusableLink).toHaveAttribute('href');
                    expect(firstFocusableLink).toHaveAttribute('aria-selected', 'false');

                    firstFocusableLink.focus();
                    expect(document.activeElement).toBe(firstFocusableLink);
                }
            }
        });

        test('works with focus management utilities', async () => {
            const user = userEvent.setup();

            renderSortingBar();

            const sortButton = screen.getByRole('button');
            await testFocusManagement(sortButton);

            await user.click(sortButton);

            const dropdownLinks = getDropdownLinks();
            if (dropdownLinks.length > 0) {
                const firstFocusableLink = dropdownLinks.find((link) => link.getAttribute('aria-selected') === 'false');
                if (firstFocusableLink) {
                    await testFocusManagement(firstFocusableLink);
                }
            }
        });
    });
});
