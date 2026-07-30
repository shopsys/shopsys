import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { ProductDetailSectionNavigation } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailSectionNavigation';
import type { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { createRef, type ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

let boundaryTop = 1_000;

vi.mock('components/Basic/HorizontalScrollHint/HorizontalScrollHint', () => ({
    HorizontalScrollHint: ({
        render: renderContent,
    }: {
        render: (ref: React.RefObject<HTMLDivElement | null>) => ReactNode;
    }) => renderContent(createRef()),
}));

vi.mock('components/Basic/Tag/Tag', () => ({
    Tag: ({ children, onClick }: { children: ReactNode; onClick: () => void }) => (
        <button type="button" onClick={onClick}>
            {children}
        </button>
    ),
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('utils/ui/useMediaMin', () => ({
    useMediaMin: () => false,
}));

vi.mock('components/Pages/ProductDetail/ProductDetailSections/ProductDetailStickyAction', () => ({
    ProductDetailStickyAction: ({ isVisible }: { isVisible: boolean }) => (
        <div data-testid="sticky-action" data-visible={isVisible} />
    ),
}));

describe('ProductDetailSectionNavigation', () => {
    beforeEach(() => {
        boundaryTop = 1_000;
        vi.spyOn(window, 'requestAnimationFrame').mockImplementation((callback) =>
            window.setTimeout(() => callback(0), 0),
        );
        vi.spyOn(window, 'cancelAnimationFrame').mockImplementation((animationFrameId) =>
            window.clearTimeout(animationFrameId),
        );
        vi.spyOn(HTMLElement.prototype, 'getBoundingClientRect').mockImplementation(function (this: HTMLElement) {
            const top = this.dataset.testid === 'sticky-boundary' ? boundaryTop : -1;

            return { bottom: top, height: 0, left: 0, right: 0, top, width: 0, x: 0, y: top, toJSON: vi.fn() };
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    test('keeps the mobile sticky action hidden during viewport boundary fluctuations', async () => {
        const stickyActionBoundaryRef = createRef<HTMLDivElement>();

        render(
            <>
                <ProductDetailSectionNavigation
                    activeSection={null}
                    product={{} as TypeProductDetailFragment}
                    sections={[{ id: 'overview', label: 'Overview' }]}
                    stickyActionBoundaryRef={stickyActionBoundaryRef}
                    onSectionClick={vi.fn()}
                />
                <div data-testid="sticky-boundary" ref={stickyActionBoundaryRef} />
            </>,
        );

        await waitFor(() => expect(screen.getByTestId('sticky-action')).toHaveAttribute('data-visible', 'true'));

        boundaryTop = window.innerHeight;
        fireEvent.scroll(window);

        await waitFor(() => expect(screen.getByTestId('sticky-action')).toHaveAttribute('data-visible', 'false'));

        boundaryTop = window.innerHeight + 40;
        fireEvent.resize(window);

        await waitFor(() => expect(screen.getByTestId('sticky-action')).toHaveAttribute('data-visible', 'false'));

        boundaryTop = window.innerHeight + 100;
        fireEvent.scroll(window);

        await waitFor(() => expect(screen.getByTestId('sticky-action')).toHaveAttribute('data-visible', 'true'));
    });
});
