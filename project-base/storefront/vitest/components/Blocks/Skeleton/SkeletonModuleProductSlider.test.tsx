import { render } from '@testing-library/react';
import { SkeletonModuleProductSlider } from 'components/Blocks/Skeleton/SkeletonModuleProductSlider';
import { describe, expect, test } from 'vitest';

describe('SkeletonModuleProductSlider', () => {
    test('matches the responsive height of basket popup product cards', () => {
        const { container } = render(
            <SkeletonModuleProductSlider
                isHeadingHidden
                productItemProps={{
                    size: 'medium',
                    visibleItemsConfig: {
                        price: true,
                        addToCart: true,
                        flags: true,
                        storeAvailability: true,
                    },
                }}
                variant="basketPopup"
                visibleSliderItems={4}
            />,
        );

        const productSkeletons = container.querySelectorAll('.bg-skeleton-less');
        const firstProductSkeleton = productSkeletons.item(0);
        const responsiveTextSkeletons = Array.from(firstProductSkeleton.children).filter((element) =>
            element.classList.contains('min-h-15'),
        );

        expect(productSkeletons).toHaveLength(4);
        expect(firstProductSkeleton).toHaveClass('border', 'border-transparent');
        expect(responsiveTextSkeletons).toHaveLength(2);
        responsiveTextSkeletons.forEach((textSkeleton) => {
            expect(textSkeleton).toHaveClass('min-h-15', 'sm:min-h-10');
            expect(textSkeleton.lastElementChild).toHaveClass('sm:hidden');
        });
    });

    test('does not apply basket popup height adjustments to a regular medium product slider', () => {
        const { container } = render(
            <SkeletonModuleProductSlider
                isHeadingHidden
                productItemProps={{
                    size: 'medium',
                    visibleItemsConfig: {
                        price: true,
                        addToCart: true,
                        storeAvailability: true,
                    },
                }}
                visibleSliderItems={1}
            />,
        );

        const productSkeleton = container.querySelector('.bg-skeleton-less')!;
        const textSkeletons = Array.from(productSkeleton.children).filter((element) =>
            element.classList.contains('min-h-15'),
        );

        expect(productSkeleton).not.toHaveClass('border', 'border-transparent');
        expect(textSkeletons).toHaveLength(2);
        textSkeletons.forEach((textSkeleton) => {
            expect(textSkeleton).not.toHaveClass('sm:min-h-10');
            expect(textSkeleton.lastElementChild).not.toHaveClass('sm:hidden');
        });
    });
});
