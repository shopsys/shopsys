import { render, screen } from '@testing-library/react';
import { ProductDetailSections } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailSections';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Pages/ProductDetail/ProductDetailSections/ProductDetailSectionNavigation', () => ({
    ProductDetailSectionNavigation: ({ sections }: { sections: Array<{ id: string; label: string }> }) => (
        <nav>
            {sections.map((section) => (
                <button key={section.id} type="button">
                    {section.label}
                </button>
            ))}
        </nav>
    ),
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: { settings: { productReviewsEnabled: false } } }],
}));

vi.mock('utils/ui/useHashNavigation', () => ({
    useHashNavigation: () => ({ activeSection: null, scrollToSection: vi.fn() }),
}));

const renderProductDetailSections = (description: string | null) =>
    render(
        <ProductDetailSections
            description={description}
            files={[]}
            parameters={[]}
            productFullName="Product"
            productUuid="product-uuid"
            relatedProducts={[]}
        />,
    );

describe('ProductDetailSections', () => {
    test.each([null, '', '   '])('hides overview when description is empty', (description) => {
        const { container } = renderProductDetailSections(description);

        expect(screen.queryByRole('button', { name: 'Overview' })).not.toBeInTheDocument();
        expect(screen.queryByRole('heading', { name: 'Overview' })).not.toBeInTheDocument();
        expect(container.querySelector('#overview')).not.toBeInTheDocument();
    });

    test('shows overview navigation and content when description is present', () => {
        renderProductDetailSections('<p>Product description</p>');

        expect(screen.getByRole('button', { name: 'Overview' })).toBeInTheDocument();
        expect(screen.getByRole('heading', { name: 'Overview' })).toBeInTheDocument();
        expect(screen.getByText('Product description')).toBeInTheDocument();
    });
});
