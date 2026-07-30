import { render, screen } from '@testing-library/react';
import { Image } from 'components/Basic/Image/Image';
import type { ComponentProps } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next/image', () => ({
    default: ({ alt, unoptimized }: ComponentProps<'img'> & { unoptimized?: boolean }) => (
        <span aria-label={alt} data-unoptimized={String(!!unoptimized)} role="img" />
    ),
}));

describe('Image', () => {
    test('skips responsive variants for SVG images', () => {
        render(<Image alt="Vector logo" height={32} src="/images/logo.svg" width={64} />);

        expect(screen.getByRole('img', { name: 'Vector logo' })).toHaveAttribute('data-unoptimized', 'true');
    });

    test('keeps raster images optimized', () => {
        render(<Image alt="Product" height={32} src="/images/product.webp" width={64} />);

        expect(screen.getByRole('img', { name: 'Product' })).toHaveAttribute('data-unoptimized', 'false');
    });
});
