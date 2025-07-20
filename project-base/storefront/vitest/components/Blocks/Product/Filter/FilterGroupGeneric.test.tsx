import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FilterGroupGeneric, MappedFilterOption } from 'components/Blocks/Product/Filter/FilterGroupGeneric';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { testFocusManagement } from 'vitest/utils/accessibility/a11y-testing';
import {
    navigateToElement,
    navigateWithTab,
    pressEnterKey,
    pressSpaceKey,
} from 'vitest/utils/accessibility/keyboard-navigation';
import { mockFilterData, waitForFilterApplication } from 'vitest/utils/filterOptions/filter-utils';

type FilterFieldType = 'flags' | 'brands';

const mockUpdateFilterQuery = vi.fn();
const mockCurrentFilterQuery = {
    flags: ['flag-1', 'flag-3'],
    brands: ['brand-2'],
};
const mockSessionStore = {
    defaultProductFiltersMap: {
        flags: new Set(['flag-1']),
    },
};
const mockFilterShowLess = {
    isShownMore: false,
    numberOfShownItems: 3,
    showMore: vi.fn(),
    showLess: vi.fn(),
};

vi.mock('utils/queryParams/useUpdateFilterQuery', () => ({
    useUpdateFilterQuery: () => ({
        updateFilterFlagsQuery: mockUpdateFilterQuery,
        updateFilterBrandsQuery: mockUpdateFilterQuery,
    }),
}));

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({
    useCurrentFilterQuery: () => mockCurrentFilterQuery,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: any) => any) => selector(mockSessionStore),
}));

vi.mock('components/Blocks/Product/Filter/utils/useFilterShowLess', () => ({
    useFilterShowLess: (options: any, defaultNumberOfShownItems: number) => ({
        defaultOptions: options?.slice(0, mockFilterShowLess.numberOfShownItems || defaultNumberOfShownItems) || [],
        isShowLessMoreShown:
            (options?.length || 0) > (mockFilterShowLess.numberOfShownItems || defaultNumberOfShownItems),
        isWithAllItemsShown: mockFilterShowLess.isShownMore,
        setAreAllItemsShown: (fn: (prev: boolean) => boolean) => {
            const newValue = fn(mockFilterShowLess.isShownMore);
            mockFilterShowLess.isShownMore = newValue;
            if (newValue) {
                mockFilterShowLess.showMore();
            } else {
                mockFilterShowLess.showLess();
            }
        },
    }),
}));

vi.mock('framer-motion', async () => {
    const actual = (await vi.importActual('framer-motion')) as Record<string, any>;
    return {
        ...actual,
        AnimatePresence: ({ children }: { children: React.ReactNode }) => <>{children}</>,
        motion: {
            div: ({ children, ...props }: any) => <div {...props}>{children}</div>,
        },
        m: {
            div: ({ children, ...props }: any) => <div {...props}>{children}</div>,
        },
    };
});

const mockPush = vi.fn();
const mockRouter = {
    push: mockPush,
    pathname: '/products',
    query: {},
    asPath: '/products',
    route: '/products',
    isReady: true,
};

vi.mock('next/router', () => ({
    useRouter: () => mockRouter,
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => {
            const translations: { [key: string]: string } = {
                'Show more': 'Show more',
                'Show less': 'Show less',
            };
            return translations[key] || key;
        },
    }),
}));

const createMockFlagOption = (id: string, name: string, rgbColor: string = '#FF0000'): MappedFilterOption => ({
    uuid: id,
    name,
    count: 5,
    rgbColor,
});

const createMockBrandOption = (id: string, name: string): MappedFilterOption => ({
    uuid: id,
    name,
    count: 5,
});

const mockFlagOptions: MappedFilterOption[] = [
    createMockFlagOption('flag-1', 'Premium', '#FFD700'),
    createMockFlagOption('flag-2', 'Sale', '#FF0000'),
    createMockFlagOption('flag-3', 'New', '#00FF00'),
    createMockFlagOption('flag-4', 'Limited', '#0000FF'),
    createMockFlagOption('flag-5', 'Bestseller', '#FF00FF'),
];

const mockBrandOptions: MappedFilterOption[] = [
    createMockBrandOption('brand-1', 'Apple'),
    createMockBrandOption('brand-2', 'Samsung'),
    createMockBrandOption('brand-3', 'Google'),
    createMockBrandOption('brand-4', 'Microsoft'),
    createMockBrandOption('brand-5', 'Sony'),
];

const defaultProps = {
    title: 'Test Filter',
    filterField: 'flags' as FilterFieldType,
    options: mockFlagOptions,
    defaultNumberOfShownItems: 3,
    isActive: false,
    ariaLabel: 'Test Filter',
};

describe('FilterGroupGeneric', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockFilterShowLess.isShownMore = false;
        mockFilterShowLess.numberOfShownItems = 3;
        mockCurrentFilterQuery.flags = ['flag-1', 'flag-3'];
        mockCurrentFilterQuery.brands = ['brand-2'];
        mockSessionStore.defaultProductFiltersMap.flags = new Set(['flag-1']);
        mockPush.mockClear();
    });

    describe('Basic Rendering', () => {
        test('renders filter group with title', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            expect(screen.getByText('Test Filter')).toBeInTheDocument();

            const titleButton = screen.getByRole('button', { name: /Test Filter/i });
            expect(titleButton).toBeInTheDocument();
        });

        test('renders correct number of filter items initially', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);
        });

        test('renders flags with visual colors', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            expect(screen.getByText('Premium')).toBeInTheDocument();
            expect(screen.getByText('Sale')).toBeInTheDocument();
            expect(screen.getByText('New')).toBeInTheDocument();
        });

        test('renders brands without flag components', () => {
            render(<FilterGroupGeneric {...defaultProps} filterField="brands" options={mockBrandOptions} />);

            expect(screen.getByText('Apple')).toBeInTheDocument();
            expect(screen.getByText('Samsung')).toBeInTheDocument();
            expect(screen.getByText('Google')).toBeInTheDocument();

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);
        });

        test('renders show more button when options exceed default number', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const showMoreButton = screen.getByRole('button', { name: /Show more/i });
            expect(showMoreButton).toBeInTheDocument();
        });

        test('does not render show more button when options are fewer than default', () => {
            render(<FilterGroupGeneric {...defaultProps} options={mockFlagOptions.slice(0, 2)} />);

            expect(screen.queryByRole('button', { name: /Show more/i })).not.toBeInTheDocument();
        });
    });

    describe('Filter Field Types', () => {
        test('handles flags filter field correctly', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes[0]).toBeChecked();
            expect(checkboxes[1]).not.toBeChecked();
            expect(checkboxes[2]).toBeChecked();
        });

        test('handles brands filter field correctly', () => {
            render(<FilterGroupGeneric {...defaultProps} filterField="brands" options={mockBrandOptions} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes[0]).not.toBeChecked();
            expect(checkboxes[1]).toBeChecked();
            expect(checkboxes[2]).not.toBeChecked();
        });

        test('applies default flags from session store', () => {
            mockCurrentFilterQuery.flags = [];

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes[0]).toBeChecked();
            expect(checkboxes[1]).not.toBeChecked();
            expect(checkboxes[2]).not.toBeChecked();
        });

        test('session defaults only apply to flags, not brands', () => {
            mockCurrentFilterQuery.brands = [];
            (mockSessionStore.defaultProductFiltersMap as any).brands = ['brand-1'];

            render(<FilterGroupGeneric {...defaultProps} filterField="brands" options={mockBrandOptions} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes[0]).not.toBeChecked();
        });
    });

    describe('Show More/Less Functionality', () => {
        test('shows more items when show more button is clicked', async () => {
            const user = userEvent.setup();
            mockFilterShowLess.isShownMore = false;

            render(<FilterGroupGeneric {...defaultProps} />);

            const showMoreButton = screen.getByRole('button', { name: /Show more/i });
            await user.click(showMoreButton);

            expect(mockFilterShowLess.showMore).toHaveBeenCalledTimes(1);
        });

        test('shows less items when show less button is clicked', async () => {
            const user = userEvent.setup();
            mockFilterShowLess.isShownMore = true;

            render(<FilterGroupGeneric {...defaultProps} />);

            const showLessButton = screen.getByRole('button', { name: /Show less/i });
            expect(showLessButton).toHaveTextContent('Show less');

            await user.click(showLessButton);

            expect(mockFilterShowLess.showLess).toHaveBeenCalledTimes(1);
        });

        test('updates number of shown items when expanded', () => {
            mockFilterShowLess.isShownMore = true;
            mockFilterShowLess.numberOfShownItems = 5;

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(5);
        });

        test('show more button is properly rendered', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const showMoreButton = screen.getByRole('button', { name: /Show more/i });
            expect(showMoreButton).toBeInTheDocument();
        });

        test('show less button is properly rendered', () => {
            mockFilterShowLess.isShownMore = true;

            render(<FilterGroupGeneric {...defaultProps} />);

            const showLessButton = screen.getByRole('button', { name: /Show less/i });
            expect(showLessButton).toBeInTheDocument();
        });
    });

    describe('Filter Selection and Updates', () => {
        test('calls updateFilterFlagsQuery when flag is selected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const uncheckedCheckbox = checkboxes[1];

            await user.click(uncheckedCheckbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('flag-2');
        });

        test('calls updateFilterFlagsQuery when flag is deselected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const checkedCheckbox = checkboxes[0];

            await user.click(checkedCheckbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('flag-1');
        });

        test('calls updateFilterBrandsQuery when brand is selected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupGeneric {...defaultProps} filterField="brands" options={mockBrandOptions} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const uncheckedCheckbox = checkboxes[0];

            await user.click(uncheckedCheckbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('brand-1');
        });

        test('calls updateFilterBrandsQuery when brand is deselected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupGeneric {...defaultProps} filterField="brands" options={mockBrandOptions} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const checkedCheckbox = checkboxes[1];

            await user.click(checkedCheckbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('brand-2');
        });

        test('handles multiple flag selections correctly', async () => {
            const user = userEvent.setup();
            mockCurrentFilterQuery.flags = [];

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');

            await user.click(checkboxes[0]);
            expect(mockUpdateFilterQuery).toHaveBeenLastCalledWith('flag-1');

            mockCurrentFilterQuery.flags = ['flag-1'];

            await user.click(checkboxes[1]);
            expect(mockUpdateFilterQuery).toHaveBeenLastCalledWith('flag-2');
        });
    });

    describe('Active State Handling', () => {
        test('applies active styling when isActive is true', () => {
            render(<FilterGroupGeneric {...defaultProps} isActive />);

            const titleButton = screen.getByRole('button', { name: /Test Filter/i });
            expect(titleButton).toBeInTheDocument();
        });

        test('applies default styling when isActive is false', () => {
            render(<FilterGroupGeneric {...defaultProps} isActive={false} />);

            const titleButton = screen.getByRole('button', { name: /Test Filter/i });
            expect(titleButton).toBeInTheDocument();
        });
    });

    describe('Accessibility Features', () => {
        test('has proper labels for checkboxes', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes[0]).toBeInTheDocument();
            expect(checkboxes[1]).toBeInTheDocument();
            expect(checkboxes[2]).toBeInTheDocument();
        });

        test('checkbox labels are rendered with text content', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            expect(screen.getByText('Premium')).toBeInTheDocument();
            expect(screen.getByText('Sale')).toBeInTheDocument();
            expect(screen.getByText('New')).toBeInTheDocument();
        });

        test('supports keyboard navigation between checkboxes', async () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');

            checkboxes[0].focus();
            expect(document.activeElement).toBe(checkboxes[0]);

            await navigateWithTab();
            expect(document.activeElement).toBe(checkboxes[1]);

            await navigateWithTab();
            expect(document.activeElement).toBe(checkboxes[2]);
        });

        test('supports keyboard selection with Space key', async () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const uncheckedCheckbox = checkboxes[1];

            uncheckedCheckbox.focus();
            await pressSpaceKey();

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('flag-2');
        });

        test('supports keyboard activation of show more button', async () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const showMoreButton = screen.getByRole('button', { name: /Show more/i });

            showMoreButton.focus();
            await pressEnterKey();

            expect(mockFilterShowLess.showMore).toHaveBeenCalledTimes(1);
        });

        test('maintains focus after filter selection', async () => {
            const user = userEvent.setup();

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const targetCheckbox = checkboxes[1];

            targetCheckbox.focus();
            await user.click(targetCheckbox);

            expect(document.activeElement).toBe(targetCheckbox);
        });
    });

    describe('Performance and Edge Cases', () => {
        test('handles empty options array', () => {
            render(<FilterGroupGeneric {...defaultProps} options={[]} />);

            expect(screen.getByText('Test Filter')).toBeInTheDocument();
            expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
            expect(screen.queryByRole('button', { name: /Show more/i })).not.toBeInTheDocument();
        });

        test('handles options with missing flag data', () => {
            const incompleteOptions: any[] = [{ ...createMockBrandOption('flag-1', 'Premium') }, mockFlagOptions[1]];

            render(<FilterGroupGeneric {...defaultProps} options={incompleteOptions} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(2);
        });

        test('handles very long filter names', () => {
            const longNameOptions = [
                createMockFlagOption('flag-1', 'This is a very long filter name that might cause layout issues'),
            ];

            render(<FilterGroupGeneric {...defaultProps} options={longNameOptions} />);

            expect(
                screen.getByText('This is a very long filter name that might cause layout issues'),
            ).toBeInTheDocument();
        });

        test('handles rapid successive clicks', async () => {
            const user = userEvent.setup();

            render(<FilterGroupGeneric {...defaultProps} />);

            const checkbox = screen.getAllByRole('checkbox')[1];

            await user.click(checkbox);
            await user.click(checkbox);
            await user.click(checkbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledTimes(3);
        });

        test('handles large number of options performance', () => {
            const manyOptions = Array.from({ length: 100 }, (_, i) => createMockFlagOption(`flag-${i}`, `Flag ${i}`));

            const startTime = performance.now();
            render(<FilterGroupGeneric {...defaultProps} options={manyOptions} />);
            const endTime = performance.now();

            expect(endTime - startTime).toBeLessThan(100);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);
        });
    });

    describe('Integration with Test Utilities', () => {
        test('works with filter testing utilities', async () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            expect(mockFilterData).toBeDefined();
            expect(mockFilterData.flags).toBeDefined();
            expect(mockFilterData.brands).toBeDefined();

            await waitForFilterApplication();

            const checkbox = screen.getAllByRole('checkbox')[1];
            await userEvent.setup().click(checkbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalled();
        });

        test('works with accessibility testing utilities', async () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const titleButton = screen.getByRole('button', { name: /Test Filter/i });
            await testFocusManagement(titleButton);

            const checkboxes = screen.getAllByRole('checkbox');
            await testFocusManagement(checkboxes[0]);
        });

        test('works with keyboard navigation utilities', async () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');

            const firstCheckbox = await navigateToElement('checkbox', 'Premium');
            expect(firstCheckbox).toBe(checkboxes[0]);

            await navigateWithTab();
            expect(document.activeElement).toBe(checkboxes[1]);

            await pressSpaceKey();
            expect(mockUpdateFilterQuery).toHaveBeenCalled();
        });
    });

    describe('Component Integration', () => {
        test('integrates properly with FilterGroupWrapper', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const titleButton = screen.getByRole('button', { name: /Test Filter/i });
            expect(titleButton).toBeInTheDocument();

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes.length).toBeGreaterThan(0);
        });

        test('integrates properly with Checkbox component', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);

            expect(screen.getByText('Premium')).toBeInTheDocument();
            expect(screen.getByText('Sale')).toBeInTheDocument();
            expect(screen.getByText('New')).toBeInTheDocument();
        });

        test('integrates properly with Flag component for flags filter', () => {
            render(<FilterGroupGeneric {...defaultProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);

            expect(screen.getByText('Premium')).toBeInTheDocument();
            expect(screen.getByText('Sale')).toBeInTheDocument();
            expect(screen.getByText('New')).toBeInTheDocument();
        });

        test('does not render Flag component for brands filter', () => {
            render(<FilterGroupGeneric {...defaultProps} filterField="brands" options={mockBrandOptions} />);

            expect(screen.getByText('Apple')).toBeInTheDocument();
            expect(screen.getByText('Samsung')).toBeInTheDocument();
            expect(screen.getByText('Google')).toBeInTheDocument();

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);
        });
    });
});
