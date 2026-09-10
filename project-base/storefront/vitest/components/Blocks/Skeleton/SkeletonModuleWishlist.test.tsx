import { render, screen } from '@testing-library/react';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Skeleton/SkeletonModuleProductListItem', () => ({
    SkeletonModuleProductListItem: () => <div data-testid="wishlist-skeleton-item" />,
}));

describe('SkeletonModuleWishlist', () => {
    test('renders five items in five columns on extra wide screens', () => {
        render(<SkeletonModuleWishlist />);

        const skeletonItems = screen.getAllByTestId('wishlist-skeleton-item');

        expect(skeletonItems).toHaveLength(5);
        expect(skeletonItems[0].parentElement).toHaveClass('xxl:grid-cols-5');
    });

    test('renders placeholders for the wishlist header and controls', () => {
        const { container } = render(<SkeletonModuleWishlist />);

        expect(container.querySelectorAll('.custom-loading-skeleton')).toHaveLength(5);
    });
});
