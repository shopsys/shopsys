import { fireEvent, screen, within } from '@testing-library/react';
import { ProductComparison } from 'components/Pages/ProductComparison/ProductComparison';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { beforeEach, expect, test, vi } from 'vitest';
import { createProduct } from '../../helpers/productFixture';
import { renderWithTooltipProvider as render } from '../../helpers/renderWithTooltipProvider';

const state = vi.hoisted(() => ({
    products: [] as TypeProductInProductListFragment[],
    choose: vi.fn(),
    remove: vi.fn(),
}));
vi.mock('utils/productLists/comparison/useComparisonPage', () => ({
    useComparisonPage: () => ({
        products: state.products,
        isLoading: false,
        emptyStateRef: { current: null },
        handleRemove: state.remove,
        handleRemoveAll: vi.fn(),
        moveProduct: vi.fn(),
    }),
}));
vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: () => ({ chooseVariantForComparison: state.choose }),
}));
vi.mock('components/Pages/ProductComparison/ProductComparisonContent', () => ({
    ProductComparisonContent: ({
        comparedProducts,
        onRemove,
    }: {
        comparedProducts: TypeProductInProductListFragment[];
        onRemove: (product: TypeProductInProductListFragment, index: number) => void;
    }) => (
        <div role="table">
            {comparedProducts.map((product, index) => (
                <div key={product.uuid}>
                    <span>{product.fullName}</span>
                    <button onClick={() => onRemove(product, index)}>Remove {product.fullName}</button>
                </div>
            ))}
        </div>
    ),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { productName?: string }) =>
            key.replace('{{ productName }}', options?.productName ?? ''),
    }),
}));
const concrete = { ...createProduct('Concrete SKU'), imagesCount: 0, parameters: [] };
const group = {
    ...createProduct('Product family'),
    __typename: 'MainVariant' as const,
    isMainVariant: true,
    variantsCount: 2,
    imagesCount: 0,
    parameters: [],
};

beforeEach(() => {
    vi.clearAllMocks();
    state.products = [concrete, group];
});

test('keeps a saved group as a selection prompt outside the concrete comparison table', () => {
    render(<ProductComparison />);

    expect(within(screen.getByRole('table')).getByText('Concrete SKU')).toBeInTheDocument();
    expect(within(screen.getByRole('table')).queryByText('Product family')).not.toBeInTheDocument();
    expect(screen.getByText('Choose a specific variant to compare its price and parameters.')).toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: 'Choose a variant to compare' }));

    expect(state.choose).toHaveBeenCalledWith(group, GtmProductListNameType.product_comparison_page, 1);
});

test('can remove the saved group and restores focus to a stable heading after its prompt disappears', () => {
    const { rerender } = render(<ProductComparison />);
    const remove = screen.getByRole('button', { name: 'Remove from comparison product Product family' });
    remove.focus();
    fireEvent.click(remove);
    expect(state.remove).toHaveBeenCalledWith(group, 1);

    state.products = [concrete];
    rerender(<ProductComparison />);

    expect(screen.getByRole('heading', { level: 1 })).toHaveFocus();
    expect(screen.queryByRole('button', { name: 'Choose a variant to compare' })).not.toBeInTheDocument();
});

test('preserves the original position for undo when a saved group precedes the removed SKU', () => {
    state.products = [group, concrete];
    render(<ProductComparison />);

    fireEvent.click(within(screen.getByRole('table')).getByRole('button', { name: 'Remove Concrete SKU' }));

    expect(state.remove).toHaveBeenCalledWith(concrete, 1);
});
