import { render, screen } from '@testing-library/react';
import { LastVisitedProductsContent } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProductsContent';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockUseProductsByCatnums = vi.fn();

vi.mock('graphql/requests/products/queries/ProductsByCatnumsQuery.generated', () => ({
    useProductsByCatnums: () => [mockUseProductsByCatnums()],
}));

vi.mock('components/Blocks/Product/ProductsSlider', () => ({
    ProductsSlider: ({ products }: { products: Array<{ catalogNumber: string }> }) => (
        <div data-testid="products-slider">
            {products.map((product) => (
                <div key={product.catalogNumber} data-testid="slider-product">
                    {product.catalogNumber}
                </div>
            ))}
        </div>
    ),
    VISIBLE_SLIDER_ITEMS_LAST_VISITED: 5,
}));

vi.mock('components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts', () => ({
    SkeletonModuleLastVisitedProducts: () => <div data-testid="last-visited-skeleton" />,
}));

const buildProducts = (count: number) =>
    Array.from({ length: count }, (_, i) => ({ catalogNumber: `product-${i + 1}` }));

describe('LastVisitedProductsContent', () => {
    beforeEach(() => {
        mockUseProductsByCatnums.mockReset();
    });

    test('displays at most 10 products even when more are returned', () => {
        mockUseProductsByCatnums.mockReturnValue({
            data: { productsByCatnums: buildProducts(15) },
            fetching: false,
        });

        render(<LastVisitedProductsContent productsCatnums={buildProducts(15).map((p) => p.catalogNumber)} />);

        const rendered = screen.getAllByTestId('slider-product');
        expect(rendered).toHaveLength(10);
        expect(rendered[0]).toHaveTextContent('product-1');
        expect(rendered.at(-1)).toHaveTextContent('product-10');
    });

    test('displays all products when fewer than 10 are returned', () => {
        mockUseProductsByCatnums.mockReturnValue({
            data: { productsByCatnums: buildProducts(7) },
            fetching: false,
        });

        render(<LastVisitedProductsContent productsCatnums={buildProducts(7).map((p) => p.catalogNumber)} />);

        expect(screen.getAllByTestId('slider-product')).toHaveLength(7);
    });

    test('renders the skeleton while products are still fetching', () => {
        mockUseProductsByCatnums.mockReturnValue({
            data: undefined,
            fetching: true,
        });

        render(<LastVisitedProductsContent productsCatnums={['product-1']} />);

        expect(screen.getByTestId('last-visited-skeleton')).toBeInTheDocument();
        expect(screen.queryByTestId('products-slider')).not.toBeInTheDocument();
    });
});
