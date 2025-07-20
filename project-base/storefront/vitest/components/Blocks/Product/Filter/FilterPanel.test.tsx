import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string, params?: any) => {
            if (key === 'products count') {
                return params?.count === 1 ? 'product' : 'products';
            }
            return key;
        },
    }),
}));

vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: any) => children,
    motion: {
        div: ({ children, ...props }: any) => <div {...props}>{children}</div>,
    },
}));

const mockSetIsFilterPanelOpen = vi.fn();
vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: any) => {
        const mockState = {
            setIsFilterPanelOpen: mockSetIsFilterPanelOpen,
        };
        return selector(mockState);
    },
}));

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({
    useCurrentFilterQuery: vi.fn(),
}));

vi.mock('utils/queryParams/useUpdateFilterQuery', () => ({
    useUpdateFilterQuery: vi.fn(),
}));

vi.mock('utils/mappers/price', () => ({
    isPriceVisible: vi.fn((price) => price !== undefined && price !== null && price !== ''),
}));

vi.mock('components/Blocks/Product/Filter/FilterGroupInStock', () => ({
    FilterGroupInStock: ({ inStockCount }: { inStockCount: number }) =>
        inStockCount > 0 ? (
            <div data-testid="filter-group-in-stock">
                <button tabIndex={0} title="In Stock (25)" type="button">
                    In Stock ({inStockCount})
                </button>
            </div>
        ) : null,
}));

vi.mock('components/Blocks/Product/Filter/FilterGroupPrice', () => ({
    FilterGroupPrice: ({
        title,
        isActive,
        initialMinPrice,
    }: {
        title: string;
        isActive: boolean;
        initialMinPrice?: string;
    }) => {
        const shouldShow = Boolean(initialMinPrice && initialMinPrice !== '');
        return shouldShow ? (
            <div data-testid="filter-group-price">
                <button tabIndex={0} title="Price (Active)" type="button">
                    {title} {isActive && '(Active)'}
                </button>
                <input aria-label="Filter by minimum value" type="number" />
                <input aria-label="Filter by maximum value" type="number" />
            </div>
        ) : null;
    },
}));

vi.mock('components/Blocks/Product/Filter/FilterGroupGeneric', () => ({
    FilterGroupGeneric: ({ title, isActive, options }: { title: string; isActive: boolean; options: any[] }) =>
        options.length ? (
            <div data-testid={`filter-group-generic-${title.toLowerCase()}`}>
                <button tabIndex={0} title={`${title} (Active)`} type="button">
                    {title} {isActive && '(Active)'}
                </button>
                {options.map((option, index) => (
                    <label key={index}>
                        <input type="checkbox" />
                        {option.name} ({option.count})
                    </label>
                ))}
            </div>
        ) : null,
}));

vi.mock('components/Blocks/Product/Filter/FilterGroupParameters', () => ({
    FilterGroupParameters: ({ title, isActive, parameter }: { title: string; isActive: boolean; parameter?: any }) =>
        parameter ? (
            <div data-testid={`filter-group-parameters-${title.toLowerCase()}`}>
                <button tabIndex={0} title={`${title} (Active)`} type="button">
                    {title} {isActive && '(Active)'}
                </button>
                <input type="checkbox" />
            </div>
        ) : null,
}));

const mockResetAllFilterQueries = vi.fn();

describe('FilterPanel Component', () => {
    const mockFilterOptions = {
        __typename: 'ProductFilterOptions' as const,
        inStock: 25,
        minimalPrice: '10',
        maximalPrice: '1000',
        flags: [
            {
                __typename: 'FlagFilterOption' as const,
                flag: {
                    __typename: 'Flag' as const,
                    name: 'Sale',
                    uuid: 'sale-uuid',
                    rgbColor: '#ff0000',
                },
                count: 15,
                isSelected: false,
            },
            {
                __typename: 'FlagFilterOption' as const,
                flag: {
                    __typename: 'Flag' as const,
                    name: 'New',
                    uuid: 'new-uuid',
                    rgbColor: '#00ff00',
                },
                count: 8,
                isSelected: false,
            },
        ],
        brands: [
            {
                __typename: 'BrandFilterOption' as const,
                brand: {
                    __typename: 'Brand' as const,
                    name: 'Apple',
                    uuid: 'apple-uuid',
                },
                count: 12,
            },
            {
                __typename: 'BrandFilterOption' as const,
                brand: {
                    __typename: 'Brand' as const,
                    name: 'Samsung',
                    uuid: 'samsung-uuid',
                },
                count: 10,
            },
        ],
        parameters: [
            {
                __typename: 'ParameterCheckboxFilterOption' as const,
                name: 'Color',
                uuid: 'color-uuid',
                isCollapsed: false,
                values: [
                    {
                        __typename: 'ParameterValueFilterOption' as const,
                        uuid: 'red-uuid',
                        text: 'Red',
                        count: 5,
                        isSelected: false,
                    },
                    {
                        __typename: 'ParameterValueFilterOption' as const,
                        uuid: 'blue-uuid',
                        text: 'Blue',
                        count: 3,
                        isSelected: false,
                    },
                ],
            },
            {
                __typename: 'ParameterCheckboxFilterOption' as const,
                name: 'Size',
                uuid: 'size-uuid',
                isCollapsed: false,
                values: [
                    {
                        __typename: 'ParameterValueFilterOption' as const,
                        uuid: 'small-uuid',
                        text: 'Small',
                        count: 7,
                        isSelected: false,
                    },
                    {
                        __typename: 'ParameterValueFilterOption' as const,
                        uuid: 'large-uuid',
                        text: 'Large',
                        count: 4,
                        isSelected: false,
                    },
                ],
            },
        ],
    };

    const defaultProps = {
        productFilterOptions: mockFilterOptions,
        defaultOrderingMode: TypeProductOrderingModeEnum.Priority,
        orderingMode: TypeProductOrderingModeEnum.Priority,
        originalSlug: null,
        slug: 'category-slug',
        totalCount: 42,
        categoryAutomatedFilters: [],
    };

    beforeEach(() => {
        vi.clearAllMocks();

        vi.mocked(useCurrentFilterQuery).mockReturnValue({
            minimalPrice: 100,
            maximalPrice: 800,
            flags: ['Sale'],
            brands: ['Apple'],
            parameters: [{ parameter: 'color-uuid', values: ['red'] }],
        } as any);

        vi.mocked(useUpdateFilterQuery).mockReturnValue({
            resetAllFilterQueries: mockResetAllFilterQueries,
        } as any);
    });

    afterEach(() => {
        cleanup();
    });

    describe('Basic Rendering', () => {
        test('renders filter panel with correct structure', () => {
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByText('Product filter')).toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Close filter panel' })).toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Show 42 products' })).toBeInTheDocument();
        });

        test('renders all filter groups when data is available', () => {
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByTestId('filter-group-in-stock')).toBeInTheDocument();
            expect(screen.getByTestId('filter-group-price')).toBeInTheDocument();
            expect(screen.getByTestId('filter-group-generic-flags')).toBeInTheDocument();
            expect(screen.getByTestId('filter-group-generic-brands')).toBeInTheDocument();
            expect(screen.getByTestId('filter-group-parameters-color')).toBeInTheDocument();
            expect(screen.getByTestId('filter-group-parameters-size')).toBeInTheDocument();
        });

        test('shows active state for filtered groups', () => {
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByText('Price (Active)')).toBeInTheDocument();
            expect(screen.getByText('Flags (Active)')).toBeInTheDocument();
            expect(screen.getByText('Brands (Active)')).toBeInTheDocument();
            expect(screen.getByText('Color (Active)')).toBeInTheDocument();
        });

        test('renders clear all button when filters are active', () => {
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByRole('button', { name: /^Clear all/ })).toBeInTheDocument();
        });

        test('hides clear all button when no filters are active', () => {
            vi.mocked(useCurrentFilterQuery).mockReturnValue(null as any);
            render(<FilterPanel {...defaultProps} />);

            expect(screen.queryByRole('button', { name: /^Clear all/ })).not.toBeInTheDocument();
        });

        test('handles missing filter data gracefully', () => {
            const propsWithoutData = {
                ...defaultProps,
                productFilterOptions: {
                    __typename: 'ProductFilterOptions' as const,
                    inStock: 0,
                    minimalPrice: '',
                    maximalPrice: '',
                    flags: null,
                    brands: null,
                    parameters: null,
                },
            };

            render(<FilterPanel {...propsWithoutData} />);

            expect(screen.getByText('Product filter')).toBeInTheDocument();
            expect(screen.queryByTestId('filter-group-in-stock')).not.toBeInTheDocument();
            expect(screen.queryByTestId('filter-group-price')).not.toBeInTheDocument();
            expect(screen.queryByTestId('filter-group-generic-flags')).not.toBeInTheDocument();
            expect(screen.queryByTestId('filter-group-generic-brands')).not.toBeInTheDocument();
        });
    });

    describe('Mobile Interface', () => {
        test('mobile close button is accessible', () => {
            render(<FilterPanel {...defaultProps} />);

            const closeButton = screen.getByRole('button', { name: 'Close filter panel' });
            expect(closeButton).toHaveAttribute('tabIndex', '0');
            expect(closeButton).toHaveAttribute('type', 'button');
            expect(closeButton).toHaveAttribute('title', 'Close filter panel');
        });

        test('mobile close button closes filter panel', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const closeButton = screen.getByRole('button', { name: 'Close filter panel' });
            await user.click(closeButton);

            expect(mockSetIsFilterPanelOpen).toHaveBeenCalledWith(false);
        });

        test('show results button closes filter panel', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const showButton = screen.getByRole('button', { name: 'Show 42 products' });
            await user.click(showButton);

            expect(mockSetIsFilterPanelOpen).toHaveBeenCalledWith(false);
        });

        test('handles singular vs plural product count correctly', () => {
            const singleProductProps = { ...defaultProps, totalCount: 1 };
            render(<FilterPanel {...singleProductProps} />);

            expect(screen.getByRole('button', { name: 'Show 1 product' })).toBeInTheDocument();
        });
    });

    describe('Clear All Functionality', () => {
        test('clear all button resets all filters', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            await user.click(clearButton);

            expect(mockResetAllFilterQueries).toHaveBeenCalled();
        });

        test('clear all button is keyboard accessible', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            clearButton.focus();
            expect(document.activeElement).toBe(clearButton);

            await user.keyboard('{Enter}');
            expect(mockResetAllFilterQueries).toHaveBeenCalled();
        });

        test('clear all button works with space key', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            clearButton.focus();
            await user.keyboard(' ');

            expect(mockResetAllFilterQueries).toHaveBeenCalled();
        });
    });

    describe('Keyboard Navigation', () => {
        test('tab navigation through filter panel header', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const closeButton = screen.getByRole('button', { name: 'Close filter panel' });
            const firstFilterButton = screen.getByRole('button', { name: 'In Stock (25)' });

            closeButton.focus();
            expect(document.activeElement).toBe(closeButton);

            await user.tab();
            expect(document.activeElement).toBe(firstFilterButton);
        });

        test('tab navigation through all filter groups', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const inStockButton = screen.getByRole('button', { name: 'In Stock (25)' });
            const priceButton = screen.getByRole('button', { name: 'Price (Active)' });
            const minInput = screen.getByRole('spinbutton', { name: 'Filter by minimum value' });
            const maxInput = screen.getByRole('spinbutton', { name: 'Filter by maximum value' });
            const flagsButton = screen.getByRole('button', { name: 'Flags (Active)' });

            inStockButton.focus();
            expect(document.activeElement).toBe(inStockButton);

            await user.tab();
            expect(document.activeElement).toBe(priceButton);

            await user.tab();
            expect(document.activeElement).toBe(minInput);

            await user.tab();
            expect(document.activeElement).toBe(maxInput);

            await user.tab();
            expect(document.activeElement).toBe(flagsButton);
        });

        test('tab navigation through filter panel footer', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const showButton = screen.getByRole('button', { name: 'Show 42 products' });
            const clearButton = screen.getByRole('button', { name: /^Clear all/ });

            showButton.focus();
            expect(document.activeElement).toBe(showButton);

            await user.tab();
            expect(document.activeElement).toBe(clearButton);
        });

        test('shift+tab navigation backwards', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            const showButton = screen.getByRole('button', { name: 'Show 42 products' });

            clearButton.focus();
            expect(document.activeElement).toBe(clearButton);

            await user.tab({ shift: true });
            expect(document.activeElement).toBe(showButton);
        });

        test('keyboard navigation within price filter', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const priceFilterButton = screen.getByRole('button', { name: 'Price (Active)' });
            const minInput = screen.getByLabelText('Filter by minimum value');
            const maxInput = screen.getByLabelText('Filter by maximum value');

            priceFilterButton.focus();
            expect(document.activeElement).toBe(priceFilterButton);

            await user.tab();
            expect(document.activeElement).toBe(minInput);

            await user.tab();
            expect(document.activeElement).toBe(maxInput);
        });
    });

    describe('Accessibility', () => {
        test('all interactive elements have proper ARIA attributes', () => {
            render(<FilterPanel {...defaultProps} />);

            const closeButton = screen.getByRole('button', { name: 'Close filter panel' });
            expect(closeButton).toHaveAttribute('title', 'Close filter panel');

            const showButton = screen.getByRole('button', { name: 'Show 42 products' });
            expect(showButton).toHaveAccessibleName();

            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            expect(clearButton).toHaveAccessibleName();
        });

        test('filter groups have proper accessibility labels', () => {
            render(<FilterPanel {...defaultProps} />);

            const filterButtons = screen
                .getAllByRole('button')
                .filter((btn) => btn.getAttribute('title')?.includes('Toggle'));

            filterButtons.forEach((button) => {
                expect(button).toHaveAttribute('tabIndex', '0');
                expect(button).toHaveAttribute('type', 'button');
                expect(button).toHaveAttribute('title');
            });
        });

        test('form inputs have proper labels', () => {
            render(<FilterPanel {...defaultProps} />);

            const minInput = screen.getByLabelText('Filter by minimum value');
            const maxInput = screen.getByLabelText('Filter by maximum value');

            expect(minInput).toHaveAccessibleName();
            expect(maxInput).toHaveAccessibleName();
        });
    });

    describe('Filter State Management', () => {
        test('reflects current filter state correctly', () => {
            const customFilter = {
                minimalPrice: 50,
                maximalPrice: 500,
                flags: ['Sale', 'New'],
                brands: ['Apple', 'Samsung'],
                parameters: [
                    { parameter: 'color-uuid', values: ['red', 'blue'] },
                    { parameter: 'size-uuid', values: ['small'] },
                ],
            };

            vi.mocked(useCurrentFilterQuery).mockReturnValue(customFilter);
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByText('Price (Active)')).toBeInTheDocument();
            expect(screen.getByText('Flags (Active)')).toBeInTheDocument();
            expect(screen.getByText('Brands (Active)')).toBeInTheDocument();
            expect(screen.getByText('Color (Active)')).toBeInTheDocument();
            expect(screen.getByText('Size (Active)')).toBeInTheDocument();
        });

        test('shows inactive state when no filters are applied', () => {
            vi.mocked(useCurrentFilterQuery).mockReturnValue({
                minimalPrice: undefined,
                maximalPrice: undefined,
                flags: [],
                brands: [],
                parameters: [],
            } as any);

            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByText('Price')).toBeInTheDocument();
            expect(screen.getByText('Flags')).toBeInTheDocument();
            expect(screen.getByText('Brands')).toBeInTheDocument();
            expect(screen.getByText('Color')).toBeInTheDocument();
            expect(screen.getByText('Size')).toBeInTheDocument();

            expect(screen.queryByText('Price (Active)')).not.toBeInTheDocument();
            expect(screen.queryByText('Flags (Active)')).not.toBeInTheDocument();
        });

        test('handles partial filter state correctly', () => {
            const partialFilter = {
                minimalPrice: 100,
                maximalPrice: undefined,
                flags: ['Sale'],
                brands: [],
                parameters: [],
            };

            vi.mocked(useCurrentFilterQuery).mockReturnValue(partialFilter as any);
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByText('Price (Active)')).toBeInTheDocument();
            expect(screen.getByText('Flags (Active)')).toBeInTheDocument();
            expect(screen.getByText('Brands')).toBeInTheDocument();
            expect(screen.getByText('Color')).toBeInTheDocument();
        });
    });

    describe('Category Automated Filters', () => {
        test('hides in stock filter when automated', () => {
            const propsWithAutomatedInStock = {
                ...defaultProps,
                categoryAutomatedFilters: ['onStock'],
            };

            render(<FilterPanel {...propsWithAutomatedInStock} />);

            expect(screen.queryByTestId('filter-group-in-stock')).not.toBeInTheDocument();
        });

        test('shows in stock filter when not automated', () => {
            render(<FilterPanel {...defaultProps} />);

            expect(screen.getByTestId('filter-group-in-stock')).toBeInTheDocument();
        });

        test('handles multiple automated filters', () => {
            const propsWithMultipleAutomated = {
                ...defaultProps,
                categoryAutomatedFilters: ['onStock', 'specialPrices'],
            };

            render(<FilterPanel {...propsWithMultipleAutomated} />);

            expect(screen.queryByTestId('filter-group-in-stock')).not.toBeInTheDocument();
            expect(screen.getByTestId('filter-group-price')).toBeInTheDocument();
        });
    });

    describe('Complex User Workflows', () => {
        test('complete filter panel interaction workflow', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const closeButton = screen.getByRole('button', { name: 'Close filter panel' });
            const priceFilterButton = screen.getByRole('button', { name: 'Price (Active)' });
            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            const showButton = screen.getByRole('button', { name: 'Show 42 products' });

            closeButton.focus();
            await user.tab();
            await user.tab();
            await user.tab();

            await user.click(priceFilterButton);

            await user.click(clearButton);
            expect(mockResetAllFilterQueries).toHaveBeenCalled();

            await user.click(showButton);
            expect(mockSetIsFilterPanelOpen).toHaveBeenCalledWith(false);
        });

        test('keyboard-only filter panel workflow', async () => {
            const user = userEvent.setup();
            render(<FilterPanel {...defaultProps} />);

            const closeButton = screen.getByRole('button', { name: 'Close filter panel' });
            closeButton.focus();

            await user.keyboard('{Enter}');
            expect(mockSetIsFilterPanelOpen).toHaveBeenCalledWith(false);

            const clearButton = screen.getByRole('button', { name: /^Clear all/ });
            clearButton.focus();
            await user.keyboard(' ');
            expect(mockResetAllFilterQueries).toHaveBeenCalled();
        });

        test('rapid filter panel state changes', () => {
            const { rerender } = render(<FilterPanel {...defaultProps} />);

            rerender(<FilterPanel {...defaultProps} totalCount={100} />);
            rerender(<FilterPanel {...defaultProps} totalCount={0} />);
            rerender(<FilterPanel {...defaultProps} totalCount={42} />);

            expect(screen.getByText('Product filter')).toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Show 42 products' })).toBeInTheDocument();
        });
    });

    describe('Performance Considerations', () => {
        test('renders efficiently with large number of filters', () => {
            const largeFilterOptions = {
                ...mockFilterOptions,
                parameters: Array.from({ length: 20 }, (_, i) => ({
                    __typename: 'ParameterCheckboxFilterOption' as const,
                    name: `Parameter ${i}`,
                    uuid: `param-${i}-uuid`,
                    isCollapsed: false,
                    values: [
                        {
                            __typename: 'ParameterValueFilterOption' as const,
                            uuid: `value-${i}a-uuid`,
                            text: `Value ${i}A`,
                            count: 5,
                            isSelected: false,
                        },
                        {
                            __typename: 'ParameterValueFilterOption' as const,
                            uuid: `value-${i}b-uuid`,
                            text: `Value ${i}B`,
                            count: 3,
                            isSelected: false,
                        },
                    ],
                })),
            };

            const propsWithManyFilters = {
                ...defaultProps,
                productFilterOptions: largeFilterOptions,
            };

            const startTime = performance.now();
            render(<FilterPanel {...propsWithManyFilters} />);
            const endTime = performance.now();

            expect(endTime - startTime).toBeLessThan(100);
            expect(screen.getByText('Product filter')).toBeInTheDocument();
        });

        test('handles frequent prop updates efficiently', () => {
            const { rerender } = render(<FilterPanel {...defaultProps} />);

            for (let i = 0; i < 10; i++) {
                rerender(<FilterPanel {...defaultProps} totalCount={i + 1} />);
            }

            expect(screen.getByText('Product filter')).toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Show 10 products' })).toBeInTheDocument();
        });
    });
});
