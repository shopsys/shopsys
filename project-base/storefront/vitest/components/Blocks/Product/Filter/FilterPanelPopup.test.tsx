import { act, render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FilterPanel, FilterPanelProps } from 'components/Blocks/Product/Filter/FilterPanel';
import { TypeCategoryAutomatedFilterEnum } from 'graphql/types';
import Router from 'next/router';
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
vi.mock('components/Blocks/Product/Filter/FilterGroupInStock', () => ({
    FilterGroupInStock: () => <div>In stock</div>,
}));
vi.mock('components/Blocks/Product/Filter/FilterGroupPrice', () => ({
    FilterGroupPrice: () => <div>Price</div>,
}));
vi.mock('components/Blocks/Product/Filter/FilterGroupGeneric', () => ({
    FilterGroupGeneric: ({ title }: { title: string }) => <div>{title}</div>,
}));
vi.mock('components/Blocks/Product/Filter/FilterGroupParameters', () => ({
    FilterGroupParameters: ({ title }: { title: string }) => <button type="button">{title}</button>,
}));

const props: FilterPanelProps = {
    orderingMode: null,
    originalSlug: null,
    slug: 'category',
    totalCount: 42,
    productFilterOptions: {
        __typename: 'ProductFilterOptions',
        visibleFilterParametersCount: 5,
        inStock: 1,
        minimalPrice: '10',
        maximalPrice: '100',
        brands: [],
        flags: [],
        parameters: Array.from({ length: 7 }, (_, index) => ({
            __typename: 'ParameterCheckboxFilterOption',
            uuid: `parameter-${index}`,
            name: `Parameter ${index}`,
            isCollapsed: false,
            values: [
                {
                    __typename: 'ParameterValueFilterOption',
                    uuid: `value-${index}`,
                    text: `Value ${index}`,
                    count: 1,
                    isSelected: false,
                },
            ],
        })),
    },
};

const resetAllFilterQueries = vi.fn();

beforeAll(async () => {
    await import('components/Layout/Popup/Popup');
    await import('components/Basic/Overlay/Overlay');
});

beforeEach(() => {
    useSessionStore.setState({
        isFilterPanelOpen: false,
        storedFocusElement: null,
        defaultProductFiltersMap: getEmptyDefaultProductFiltersMap(),
    });
    vi.mocked(useMediaMin).mockReturnValue(true);
    vi.mocked(useCurrentFilterQuery).mockReturnValue({ onlyInStock: true });
    vi.mocked(useUpdateFilterQuery).mockReturnValue({
        updateFilterInStockQuery: vi.fn(),
        updateFilterPricesQuery: vi.fn(),
        updateFilterPriceMaximumQuery: vi.fn(),
        updateFilterPriceMinimumQuery: vi.fn(),
        updateFilterBrandsQuery: vi.fn(),
        updateFilterFlagsQuery: vi.fn(),
        updateFilterParametersQuery: vi.fn(),
        resetAllFilterQueries,
    });
    vi.stubGlobal(
        'ResizeObserver',
        class {
            observe() {}
            disconnect() {}
        },
    );
});

describe('Desktop filter popup', () => {
    test('limits the sidebar to the first five parameters in category order', () => {
        const parameters = props.productFilterOptions.parameters ?? [];
        const { rerender } = render(<FilterPanel {...props} />);
        const sidebar = screen.getByRole('region', { name: 'Product filters' });

        expect(
            within(sidebar)
                .getAllByRole('button', { name: /^Parameter/ })
                .map((button) => button.textContent),
        ).toEqual(['Parameter 0', 'Parameter 1', 'Parameter 2', 'Parameter 3', 'Parameter 4']);
        expect(within(sidebar).getAllByRole('button', { name: 'All filters (9)' })).toHaveLength(1);

        rerender(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    parameters: [
                        parameters[6],
                        parameters[4],
                        parameters[2],
                        parameters[0],
                        parameters[5],
                        parameters[1],
                        parameters[3],
                    ],
                }}
            />,
        );

        expect(
            within(sidebar)
                .getAllByRole('button', { name: /^Parameter/ })
                .map((button) => button.textContent),
        ).toEqual(['Parameter 6', 'Parameter 4', 'Parameter 2', 'Parameter 0', 'Parameter 5']);
        expect(within(sidebar).queryByRole('button', { name: 'Parameter 1' })).not.toBeInTheDocument();
    });

    test.each([0, 2, 5, 20])('shows the configured %i parameters and keeps all groups in the popup', async (count) => {
        const user = userEvent.setup();
        render(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    visibleFilterParametersCount: count,
                }}
            />,
        );
        const sidebar = screen.getByRole('region', { name: 'Product filters' });
        expect(within(sidebar).queryAllByRole('button', { name: /^Parameter/ })).toHaveLength(Math.min(count, 7));
        expect(within(sidebar).getByText('Price')).toBeInTheDocument();
        expect(within(sidebar).getByText('In stock')).toBeInTheDocument();

        await user.click(within(sidebar).getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');

        expect(within(popup).getAllByRole('tab')).toHaveLength(9);
        expect(within(popup).getByRole('tab', { name: 'Parameter 6' })).toBeInTheDocument();
    });

    test('retains the available primary groups in a small category', () => {
        render(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    parameters: props.productFilterOptions.parameters?.slice(0, 1) ?? null,
                }}
            />,
        );

        const sidebar = screen.getByRole('region', { name: 'Product filters' });
        expect(within(sidebar).getByRole('button', { name: 'Parameter 0' })).toBeInTheDocument();
        expect(within(sidebar).getByText('Price')).toBeInTheDocument();
        expect(within(sidebar).getByText('In stock')).toBeInTheDocument();
        expect(within(sidebar).getAllByRole('button', { name: 'All filters (3)' })).toHaveLength(1);
    });

    test('the lower shortcut opens all groups and restores focus to itself', async () => {
        const user = userEvent.setup();
        render(<FilterPanel {...props} />);
        const opener = screen.getByRole('button', { name: /^All filters/ });

        await user.click(opener);
        const popup = await screen.findByRole('dialog');
        expect(within(popup).getAllByRole('tab')).toHaveLength(9);
        await user.keyboard('{Escape}');

        await waitFor(() => expect(opener).toHaveFocus());
    });

    test('shows all groups and the current count, then restores focus after closing', async () => {
        const user = userEvent.setup();
        const { rerender } = render(<FilterPanel {...props} />);
        const opener = screen.getByRole('button', { name: /^All filters/ });

        await user.click(opener);
        const popup = await screen.findByRole('dialog', { name: 'Product filter' });

        expect(within(popup).getAllByRole('tab')).toHaveLength(9);
        expect(within(popup).getByRole('region', { name: 'Product filters' })).toBeInTheDocument();
        expect(within(popup).getByRole('heading', { name: 'Product filter' })).toHaveFocus();
        await user.click(within(popup).getByRole('tab', { name: 'Parameter 6' }));
        expect(within(popup).getByRole('tabpanel', { name: 'Parameter 6' })).toContainElement(
            within(popup).getByRole('button', { name: 'Parameter 6' }),
        );

        rerender(<FilterPanel {...props} totalCount={12} />);
        await user.click(within(popup).getByRole('button', { name: 'Show 12 products count' }));

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        expect(resetAllFilterQueries).not.toHaveBeenCalled();
        await waitFor(() => expect(opener).toHaveFocus());
    });

    test('Escape and the close button preserve applied filters', async () => {
        const user = userEvent.setup();
        render(<FilterPanel {...props} />);
        const opener = screen.getByRole('button', { name: /^All filters/ });

        await user.click(opener);
        await screen.findByRole('dialog');
        await user.keyboard('{Escape}');
        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        await waitFor(() => expect(opener).toHaveFocus());

        await user.click(opener);
        const popup = await screen.findByRole('dialog');
        await user.click(within(popup).getByRole('button', { name: 'Close popup' }));

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        expect(resetAllFilterQueries).not.toHaveBeenCalled();
    });

    test('clear all resets the URL filters and closes the popup', async () => {
        const user = userEvent.setup();
        render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');

        await user.click(within(popup).getByRole('button', { name: 'Clear all active filters' }));

        expect(resetAllFilterQueries).toHaveBeenCalledOnce();
        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    });

    test('retains an open popup and selects an available group if the selected group disappears', async () => {
        const user = userEvent.setup();
        const { rerender } = render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        await screen.findByRole('dialog');

        rerender(<FilterPanel {...props} categoryAutomatedFilters={[TypeCategoryAutomatedFilterEnum.OnStock]} />);

        expect(screen.getByRole('dialog')).toBeInTheDocument();
        expect(screen.queryByRole('tab', { name: /Availability/ })).not.toBeInTheDocument();
        expect(screen.getByRole('tabpanel', { name: 'Price' })).toBeInTheDocument();
    });

    test('closes on page navigation and keeps the next category behind the same opener', async () => {
        const user = userEvent.setup();
        const { rerender } = render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        await screen.findByRole('dialog');

        act(() => Router.events.emit('routeChangeStart', '/other-category', { shallow: false }));
        rerender(<FilterPanel {...props} categoryAutomatedFilters={[TypeCategoryAutomatedFilterEnum.OnStock]} />);

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: /^All filters/ })).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Parameter 6' })).not.toBeInTheDocument();
    });

    test('keeps the popup open during live filter and SEO-category URL updates', async () => {
        const user = userEvent.setup();
        render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        await screen.findByRole('dialog');

        act(() => Router.events.emit('routeChangeStart', '/category?onlyInStock=1', { shallow: true }));
        expect(screen.getByRole('dialog')).toBeInTheDocument();

        act(() => Router.events.emit('routeChangeStart', '/seo-category', { shallow: true }));
        expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    test('keeps the automatically selected group and focus after its last active value is cleared', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({
            parameters: [{ parameter: 'parameter-4', values: ['value-4'] }],
        });
        const { rerender } = render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        const control = within(popup).getByRole('button', { name: 'Parameter 4' });
        control.focus();

        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        rerender(<FilterPanel {...props} />);

        expect(within(popup).getByRole('tabpanel', { name: 'Parameter 4' })).toBeInTheDocument();
        expect(control).toHaveFocus();
    });

    test('supports arrow keys, Home and End in group navigation', async () => {
        const user = userEvent.setup();
        render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        const availability = within(popup).getByRole('tab', { name: /Availability/ });
        await user.click(availability);

        await user.keyboard('{End}');
        const last = within(popup).getByRole('tab', { name: 'Parameter 6' });
        expect(last).toHaveFocus();
        expect(last).toHaveAttribute('aria-selected', 'true');
        expect(within(popup).getByRole('tabpanel', { name: 'Parameter 6' })).toBeInTheDocument();

        await user.keyboard('{Home}');
        expect(availability).toHaveFocus();
        await user.keyboard('{ArrowUp}');
        expect(last).toHaveFocus();
        await user.keyboard('{ArrowDown}');
        expect(availability).toHaveFocus();
    });

    test('skips unavailable groups with arrows, Home and End', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        render(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    inStock: 0,
                    maximalPrice: '10',
                    parameters:
                        props.productFilterOptions.parameters?.map((parameter, index) =>
                            parameter.__typename === 'ParameterCheckboxFilterOption'
                                ? {
                                      ...parameter,
                                      values: parameter.values.map((value) => ({
                                          ...value,
                                          count: index === 1 || index === 3 ? 1 : 0,
                                      })),
                                  }
                                : parameter,
                        ) ?? null,
                }}
            />,
        );
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        const first = within(popup).getByRole('tab', { name: 'Parameter 1' });
        const last = within(popup).getByRole('tab', { name: 'Parameter 3' });
        const unavailable = within(popup).getByRole('tab', { name: /Parameter 2/ });
        expect(unavailable).toBeDisabled();
        expect(unavailable).toHaveAttribute('aria-disabled', 'true');
        expect(within(popup).getByRole('tabpanel', { name: 'Parameter 1' })).toBeInTheDocument();

        await user.click(first);
        await user.keyboard('{ArrowDown}');
        expect(last).toHaveFocus();
        await user.click(unavailable);
        expect(last).toHaveAttribute('aria-selected', 'true');
        await user.click(last);
        await user.keyboard('{ArrowDown}');
        expect(first).toHaveFocus();
        await user.keyboard('{End}');
        expect(last).toHaveFocus();
        await user.keyboard('{Home}');
        expect(first).toHaveFocus();
        await user.keyboard('{ArrowUp}');
        expect(last).toHaveFocus();
    });

    test('keeps one keyboard entry and an explanation when all groups are unavailable', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        render(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    inStock: 0,
                    maximalPrice: '10',
                    parameters: [],
                }}
            />,
        );
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        const selected = within(popup).getByRole('tab', { name: /Availability/ });
        expect(selected).toHaveAttribute('tabindex', '0');
        expect(selected).toHaveAttribute('aria-disabled', 'true');
        expect(selected).not.toBeDisabled();
        expect(within(popup).getByRole('status')).toHaveTextContent(
            'No options are available for the current selection.',
        );

        await user.click(selected);
        await user.keyboard('{ArrowDown}{ArrowUp}{Home}{End}');
        expect(selected).toHaveFocus();
        await user.tab();
        expect(within(popup).getByRole('button', { name: 'Show 42 products count' })).toHaveFocus();
    });

    test('keeps an unavailable selected group and restores keyboard focus when its controls disappear', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({
            parameters: [{ parameter: 'parameter-4', values: ['value-4'] }],
        });
        const { rerender } = render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        within(popup).getByRole('button', { name: 'Parameter 4' }).focus();

        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        const unavailableOptions = {
            ...props.productFilterOptions,
            parameters:
                props.productFilterOptions.parameters?.map((parameter, index) =>
                    index === 4 ? { ...parameter, values: [] } : parameter,
                ) ?? null,
        };
        rerender(<FilterPanel {...props} productFilterOptions={unavailableOptions} />);
        const selected = within(popup).getByRole('tab', { name: /Parameter 4/ });

        expect(selected).toHaveAttribute('aria-selected', 'true');
        expect(selected).toHaveAttribute('aria-disabled', 'true');
        await waitFor(() => expect(selected).toHaveFocus());
        expect(within(popup).getByRole('status')).toBeInTheDocument();
        await user.keyboard('{ArrowDown}');
        expect(within(popup).getByRole('tab', { name: 'Parameter 5' })).toHaveFocus();
    });

    test('does not move focus from the footer when the selected group becomes unavailable', async () => {
        const user = userEvent.setup();
        vi.mocked(useCurrentFilterQuery).mockReturnValue({
            parameters: [{ parameter: 'parameter-4', values: ['value-4'] }],
        });
        const { rerender } = render(<FilterPanel {...props} />);
        await user.click(screen.getByRole('button', { name: /^All filters/ }));
        const popup = await screen.findByRole('dialog');
        within(popup).getByRole('button', { name: 'Parameter 4' }).focus();
        const footer = within(popup).getByRole('button', { name: 'Show 42 products count' });
        footer.focus();

        vi.mocked(useCurrentFilterQuery).mockReturnValue(null);
        rerender(
            <FilterPanel
                {...props}
                productFilterOptions={{
                    ...props.productFilterOptions,
                    parameters:
                        props.productFilterOptions.parameters?.map((parameter, index) =>
                            index === 4 ? { ...parameter, values: [] } : parameter,
                        ) ?? null,
                }}
            />,
        );

        expect(within(popup).getByRole('status')).toBeInTheDocument();
        expect(footer).toHaveFocus();
    });

    test('keeps all groups in the existing mobile panel', () => {
        vi.mocked(useMediaMin).mockReturnValue(false);
        act(() => useSessionStore.setState({ isFilterPanelOpen: true }));

        render(<FilterPanel {...props} />);

        expect(screen.queryByRole('button', { name: /^All filters/ })).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Parameter 6' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Close filter panel' })).toBeInTheDocument();
        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    });
});
