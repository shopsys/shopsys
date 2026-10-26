import { render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FilterPanel, FilterPanelProps } from 'components/Blocks/Product/Filter/FilterPanel';
import type { ComponentType } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { getEmptyDefaultProductFiltersMap } from 'utils/seoCategories/getEmptyDefaultProductFiltersMap';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { beforeAll, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('next/router', async (importOriginal) => {
    const actual = await importOriginal<typeof import('next/router')>();
    return { ...actual, useRouter: () => ({ events: actual.default.events }) };
});

vi.mock('next/dynamic', async () => {
    const { lazy, Suspense } = await import('react');
    return {
        default: (loader: () => Promise<ComponentType<any>>) => {
            const Component = lazy(async () => ({ default: await loader() }));
            return (props: any) => (
                <Suspense fallback={null}>
                    <Component {...props} />
                </Suspense>
            );
        },
    };
});

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({ useCurrentFilterQuery: vi.fn() }));
vi.mock('utils/queryParams/useUpdateFilterQuery', () => ({ useUpdateFilterQuery: vi.fn() }));
vi.mock('utils/ui/useMediaMin', () => ({ useMediaMin: vi.fn() }));
vi.mock('components/Blocks/Product/Filter/filterElementIds', () => ({ scrollToProductListControls: vi.fn() }));
const props: FilterPanelProps = {
    orderingMode: null,
    originalSlug: null,
    slug: 'category',
    totalCount: 42,
    productFilterOptions: {
        __typename: 'ProductFilterOptions',
        visibleFilterParametersCount: 5,
        inStock: 3,
        minimalPrice: '10',
        maximalPrice: '100',
        brands: [
            { __typename: 'BrandFilterOption', count: 3, brand: { __typename: 'Brand', uuid: 'brand-lg', name: 'LG' } },
        ],
        flags: [
            {
                __typename: 'FlagFilterOption',
                count: 2,
                isSelected: false,
                flag: { __typename: 'Flag', uuid: 'sale', name: 'Action', rgbColor: '#ff0000' },
            },
        ],
        parameters: [
            {
                __typename: 'ParameterCheckboxFilterOption',
                uuid: 'material',
                name: 'Material',
                isCollapsed: false,
                values: [
                    {
                        __typename: 'ParameterValueFilterOption',
                        uuid: 'metal',
                        text: 'Metal',
                        count: 2,
                        isSelected: false,
                    },
                ],
            },
            {
                __typename: 'ParameterColorFilterOption',
                uuid: 'color',
                name: 'Color',
                isCollapsed: false,
                values: [
                    {
                        __typename: 'ParameterValueColorFilterOption',
                        uuid: 'red',
                        text: 'Red',
                        count: 2,
                        isSelected: false,
                        rgbHex: '#ff0000',
                        colorIcon: null,
                    },
                ],
            },
            {
                __typename: 'ParameterSliderFilterOption',
                singleValueCount: null,
                uuid: 'size',
                name: 'Screen size',
                isCollapsed: false,
                minimalValue: 10,
                maximalValue: 50,
                isSelectable: true,
                selectedValue: null,
                unit: null,
            },
            {
                __typename: 'ParameterCheckboxFilterOption',
                uuid: 'connection',
                name: 'Connection',
                isCollapsed: false,
                values: [],
            },
            {
                __typename: 'ParameterCheckboxFilterOption',
                uuid: 'warranty',
                name: 'Warranty',
                isCollapsed: false,
                values: [],
            },
            {
                __typename: 'ParameterCheckboxFilterOption',
                uuid: 'advanced',
                name: 'Advanced',
                isCollapsed: false,
                values: [],
            },
        ],
    },
};

beforeAll(async () => {
    await import('components/Layout/Popup/Popup');
    await import('components/Basic/Overlay/Overlay');
});

const updateFilterBrandsQuery = vi.fn();
const updateFilterInStockQuery = vi.fn();
const updateFilterFlagsQuery = vi.fn();
const updateFilterParametersQuery = vi.fn();

beforeEach(() => {
    useSessionStore.setState({
        isFilterPanelOpen: false,
        storedFocusElement: null,
        defaultProductFiltersMap: getEmptyDefaultProductFiltersMap(),
    });
    vi.mocked(useMediaMin).mockReturnValue(true);
    vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
    vi.mocked(useUpdateFilterQuery).mockReturnValue({
        updateFilterInStockQuery,
        updateFilterPricesQuery: vi.fn(),
        updateFilterPriceMaximumQuery: vi.fn(),
        updateFilterPriceMinimumQuery: vi.fn(),
        updateFilterBrandsQuery,
        updateFilterFlagsQuery,
        updateFilterParametersQuery,
        resetAllFilterQueries: vi.fn(),
    });
    vi.stubGlobal(
        'ResizeObserver',
        class {
            observe() {}
            disconnect() {}
        },
    );
});

describe('Primary filters alongside the complete popup', () => {
    test('keeps popup labels attached to their own controls for all shared filter types', async () => {
        const user = userEvent.setup();
        const { container } = render(<FilterPanel {...props} />);
        const sidebar = screen.getByRole('region', { name: 'Product filters' });
        expect(within(sidebar).queryByText('Advanced')).not.toBeInTheDocument();
        expect(within(sidebar).queryByText('Action')).not.toBeInTheDocument();
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        expect(within(popup).getByRole('tab', { name: /^Advanced/ })).toBeInTheDocument();

        for (const name of ['Availability', 'Price', 'Flags', 'Brands', 'Material', 'Color', 'Screen size']) {
            await user.click(within(popup).getByRole('tab', { name }));
            const panel = within(popup).getByRole('tabpanel');
            const labels = Array.from(panel.querySelectorAll<HTMLLabelElement>('label[for]'));
            expect(labels.length).toBeGreaterThan(0);
            for (const label of labels) {
                expect(label.control).not.toBeNull();
                expect(panel).toContainElement(label.control);
            }
            const ids = Array.from(document.querySelectorAll('[id]'), (element) => element.id);
            expect(new Set(ids).size).toBe(ids.length);
        }
        expect(container.querySelector('#filter-sidebar')).toBeInTheDocument();
    });

    test('retains zero-count stock and allows a selected stock filter to be removed', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({ onlyInStock: true });
        const zeroStockProps = { ...props, productFilterOptions: { ...props.productFilterOptions, inStock: 0 } };
        const { rerender } = render(<FilterPanel {...zeroStockProps} />);
        const sidebar = screen.getByRole('region', { name: 'Product filters' });
        expect(within(sidebar).getByRole('checkbox', { name: 'In stock' })).toBeEnabled();
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        const stock = within(popup).getByRole('checkbox', { name: 'In stock' });
        expect(stock).toBeChecked();
        await user.click(within(popup).getByText('In stock', { exact: true }));
        expect(updateFilterInStockQuery).toHaveBeenCalledExactlyOnceWith(false);

        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        rerender(<FilterPanel {...zeroStockProps} />);

        expect(within(sidebar).getByRole('checkbox', { name: 'In stock' })).toBeDisabled();
        expect(within(popup).getByRole('tab', { name: /Availability/ })).toHaveAttribute('aria-disabled', 'true');
        expect(within(popup).getByRole('status')).toBeInTheDocument();
    });

    test('synchronizes a single numeric value between sidebar and popup', async () => {
        const user = userEvent.setup();
        const filterOptions: FilterPanelProps['productFilterOptions'] = {
            ...props.productFilterOptions,
            parameters:
                props.productFilterOptions.parameters?.map((parameter) =>
                    parameter.__typename === 'ParameterSliderFilterOption'
                        ? { ...parameter, minimalValue: 5, maximalValue: 5, singleValueCount: 1 }
                        : parameter,
                ) ?? null,
        };
        const { rerender } = render(<FilterPanel {...props} productFilterOptions={filterOptions} />);
        const sidebar = screen.getByRole('region', { name: 'Product filters' });
        const sidebarCheckbox = within(sidebar).getByRole('checkbox', { name: /^5\s*\(1\)$/ });
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        await user.click(within(popup).getByRole('tab', { name: 'Screen size' }));
        const panel = within(popup).getByRole('tabpanel');
        const popupCheckbox = within(panel).getByRole('checkbox', { name: /^5\s*\(1\)$/ });
        expect(popupCheckbox.id).not.toBe(sidebarCheckbox.id);

        await user.click(within(panel).getByText('5', { exact: true }));

        expect(updateFilterParametersQuery).toHaveBeenLastCalledWith('size', undefined, 5, 5);
        vi.mocked(useCurrentFilterQuery).mockReturnValue({
            parameters: [{ parameter: 'size', minimalValue: 5, maximalValue: 5 }],
        });
        rerender(<FilterPanel {...props} productFilterOptions={filterOptions} totalCount={1} />);
        expect(sidebarCheckbox).toBeChecked();
        expect(popupCheckbox).toBeChecked();
        expect(popupCheckbox).toHaveFocus();
        await user.click(within(panel).getByText('5', { exact: true }));
        expect(updateFilterParametersQuery).toHaveBeenLastCalledWith('size', undefined, undefined, undefined);
    });

    test('restores focus when a removed choice becomes disabled but its group stays available', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({ brands: ['brand-lg'] });
        const filterOptions: FilterPanelProps['productFilterOptions'] = {
            ...props.productFilterOptions,
            brands: [
                {
                    __typename: 'BrandFilterOption',
                    count: 0,
                    brand: { __typename: 'Brand', uuid: 'brand-lg', name: 'LG' },
                },
                {
                    __typename: 'BrandFilterOption',
                    count: 1,
                    brand: { __typename: 'Brand', uuid: 'brand-other', name: 'Other' },
                },
            ],
        };
        const { rerender } = render(<FilterPanel {...props} productFilterOptions={filterOptions} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        await user.click(within(popup).getByText('LG', { exact: true }));
        expect(within(popup).getByRole('checkbox', { name: 'LG' })).toHaveFocus();

        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        rerender(<FilterPanel {...props} productFilterOptions={filterOptions} />);

        expect(within(popup).getByRole('checkbox', { name: 'LG' })).toBeDisabled();
        expect(within(popup).getByRole('checkbox', { name: /Other/ })).toBeEnabled();
        const selectedTab = within(popup).getByRole('tab', { name: 'Brands' });
        expect(selectedTab).toHaveAttribute('aria-disabled', 'false');
        await waitFor(() => expect(selectedTab).toHaveFocus());
        await user.keyboard('{ArrowDown}');
        expect(within(popup).getByRole('tab', { name: 'Material' })).toHaveFocus();
    });

    test('keeps SEO-selected zero-count flags and color values removable', async () => {
        const user = userEvent.setup();
        useSessionStore.setState({
            defaultProductFiltersMap: {
                ...getEmptyDefaultProductFiltersMap(),
                flags: new Set(['sale']),
                parameters: new Map([['color', new Set(['red'])]]),
            },
        });
        render(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    flags: props.productFilterOptions.flags?.map((option) => ({ ...option, count: 0 })) ?? null,
                    parameters:
                        props.productFilterOptions.parameters?.map((parameter) =>
                            parameter.__typename === 'ParameterColorFilterOption'
                                ? { ...parameter, values: parameter.values.map((value) => ({ ...value, count: 0 })) }
                                : parameter,
                        ) ?? null,
                }}
            />,
        );
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        const flag = within(popup).getByRole('checkbox', { name: 'Action' });
        expect(flag).toBeChecked();
        expect(flag).toBeEnabled();
        flag.focus();
        await user.keyboard('[Space]');
        expect(updateFilterFlagsQuery).toHaveBeenCalledExactlyOnceWith('sale');

        await user.click(within(popup).getByRole('tab', { name: /^Color/ }));
        const color = within(popup).getByRole('checkbox', { name: /Red/ });
        expect(color).toBeChecked();
        expect(color).toBeEnabled();
        color.focus();
        await user.keyboard('[Space]');
        expect(updateFilterParametersQuery).toHaveBeenCalledExactlyOnceWith('color', 'red');
    });

    test('keeps active unselectable sliders editable, including a zero bound', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({ parameters: [{ parameter: 'size', minimalValue: 0 }] });
        render(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    parameters:
                        props.productFilterOptions.parameters?.map((parameter) =>
                            parameter.__typename === 'ParameterSliderFilterOption'
                                ? { ...parameter, isSelectable: false }
                                : parameter,
                        ) ?? null,
                }}
            />,
        );
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');

        expect(within(popup).getByRole('tab', { name: /^Screen size/ })).toHaveAttribute('aria-disabled', 'false');
        for (const input of within(popup).getAllByRole('spinbutton')) expect(input).toBeEnabled();
        for (const slider of within(popup).getAllByRole('slider')) expect(slider).toBeEnabled();
    });

    test('updates both copies from the URL and restores focus to the lower shortcut', async () => {
        const user = userEvent.setup();
        const { rerender } = render(<FilterPanel {...props} />);
        const sidebar = screen.getByRole('region', { name: 'Product filters' });
        const opener = within(sidebar).getByRole('button', { name: /^All filters/ });
        await user.click(opener);
        const popup = await screen.findByRole('dialog');
        await user.click(within(popup).getByRole('tab', { name: 'Brands' }));
        await user.click(within(popup).getByText('LG', { exact: true }));

        expect(updateFilterBrandsQuery).toHaveBeenCalledExactlyOnceWith('brand-lg');
        expect(within(popup).getByRole('checkbox', { name: /LG/ })).toHaveFocus();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({ brands: ['brand-lg'] });
        rerender(<FilterPanel {...props} totalCount={3} />);
        expect(within(sidebar).getByRole('checkbox', { name: /LG/ })).toBeChecked();
        expect(within(popup).getByRole('checkbox', { name: /LG/ })).toBeChecked();
        await user.click(within(popup).getByRole('button', { name: 'Show 3 products count' }));

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        await waitFor(() => expect(opener).toHaveFocus());
        expect(within(sidebar).getByRole('checkbox', { name: /LG/ })).toBeChecked();
    });
});
