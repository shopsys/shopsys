import { fireEvent, render, screen } from '@testing-library/react';
import { ProductDetailFilesSection } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailFilesSection';
import { ProductDetailParametersSection } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailParametersSection';
import { ProductDetailStickyAction } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailStickyAction';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeParameterTypeEnum } from 'graphql/types';
import { createRef } from 'react';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt, src }: { alt: string; src?: string }) => <span data-alt={alt} data-src={src} />,
}));

vi.mock('components/Blocks/Product/ProductAvailability', () => ({
    ProductAvailability: ({ availableStoresCount }: { availableStoresCount: number | null }) => (
        <span>
            {availableStoresCount === null ? 'In stock' : `In stock, Ready to ship · ${availableStoresCount} store`}
        </span>
    ),
}));

vi.mock('components/Blocks/Product/ProductPrice', () => ({
    ProductPrice: () => <span>€139.96</span>,
}));

vi.mock('components/Pages/ProductDetail/ProductDetailAddToCart/DeferredProductDetailAddToCart', () => ({
    DeferredProductDetailAddToCart: () => <button type="button">Add to cart</button>,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string, options?: Record<string, string | number>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey
                        .replaceAll(`{{ ${optionKey} }}`, String(optionValue))
                        .replaceAll(`{{${optionKey}}}`, String(optionValue)),
                key,
            ),
    }),
}));

const parameter: TypeParameterFragment = {
    __typename: 'Parameter',
    group: 'Display',
    name: 'Technology',
    type: TypeParameterTypeEnum.Checkbox,
    unit: null,
    uuid: 'technology',
    values: [
        {
            __typename: 'ParameterValue',
            colorIcon: null,
            rgbHex: null,
            text: 'LED',
            uuid: 'led',
        },
    ],
};

const file: TypeFileFragment = {
    __typename: 'File',
    anchorText: 'Example manual',
    extension: 'pdf',
    filesize: 1024,
    url: '/file/example.pdf',
    viewUrl: '/file/view/example.pdf',
};

const product = {
    availability: {
        __typename: 'Availability',
        name: 'In stock',
        status: 'InStock',
    },
    availableStoresCount: 1,
    fullName: 'Television 22" Sencor',
    images: [{ __typename: 'Image', name: 'Product image', url: '/product.jpg' }],
    isInquiryType: false,
    price: {
        __typename: 'ProductPrice',
        basicPrice: {
            priceWithVat: '€139.96',
            priceWithoutVat: '€115.67',
            vatAmount: '€24.29',
        },
        isPriceFrom: false,
        nextPriceChange: null,
        percentageDiscount: null,
        priceWithoutVat: '€115.67',
        priceWithVat: '€139.96',
        vatAmount: '€24.29',
    },
    uuid: 'product-uuid',
} as TypeProductDetailFragment;

describe('Product detail accessibility', () => {
    test('uses row headers for product parameters', () => {
        render(<ProductDetailParametersSection parameters={[parameter]} sectionRef={createRef()} />);

        const rowHeader = screen.getByRole('rowheader', { name: 'Technology' });

        expect(rowHeader).toHaveAttribute('scope', 'row');
        expect(screen.getAllByText('Technology')).toHaveLength(1);
        expect(screen.getByRole('cell', { name: 'LED' })).toBeInTheDocument();
    });

    test('includes the PDF format and file size in file link names', () => {
        renderWithTooltipProvider(<ProductDetailFilesSection files={[file]} sectionRef={createRef()} />);

        expect(screen.getByRole('link', { name: 'View Example manual (1 KB PDF)' })).toHaveAttribute(
            'href',
            '/file/view/example.pdf',
        );
        expect(screen.getByRole('link', { name: 'Download Example manual (1 KB PDF)' })).toHaveAttribute(
            'href',
            '/file/example.pdf',
        );
    });

    test('uses concise tooltips for file preview and download links', () => {
        renderWithTooltipProvider(<ProductDetailFilesSection files={[file]} sectionRef={createRef()} />);
        const previewLink = screen.getByRole('link', { name: 'View Example manual (1 KB PDF)' });
        const downloadLink = screen.getByRole('link', { name: 'Download Example manual (1 KB PDF)' });

        fireEvent.focus(previewLink);

        expect(screen.getByRole('tooltip')).toHaveTextContent('Open in new tab');

        fireEvent.blur(previewLink);
        fireEvent.focus(downloadLink);

        expect(screen.getByRole('tooltip')).toHaveTextContent('Download');
    });

    test('does not repeat the sticky product name in image alt or title text', () => {
        const { container } = render(<ProductDetailStickyAction isVisible placement="inline" product={product} />);

        expect(container.querySelector('[data-alt]')).toHaveAttribute('data-alt', '');
        expect(screen.getByText(product.fullName)).not.toHaveAttribute('title');
    });

    test('shows only concise availability in the sticky action', () => {
        render(<ProductDetailStickyAction isVisible placement="inline" product={product} />);

        expect(screen.getAllByText('In stock')).toHaveLength(2);
        expect(screen.queryByText(/Ready to ship/)).not.toBeInTheDocument();
    });
});
