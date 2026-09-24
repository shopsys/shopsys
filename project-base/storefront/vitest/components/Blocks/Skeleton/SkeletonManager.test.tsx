import { render, screen, waitFor } from '@testing-library/react';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { domAnimation, LazyMotion } from 'framer-motion';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Skeleton/SkeletonPageContact', () => ({
    SkeletonPageContact: () => <div>Loading placeholder</div>,
}));

const store = vi.hoisted(() => ({
    redirectPageType: 'contact',
    updatePageLoadingState: vi.fn(),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: typeof store) => unknown) => selector(store),
}));

const renderPage = (isFetchingData: boolean) => (
    <LazyMotion features={domAnimation}>
        <SkeletonManager isFetchingData={isFetchingData} isPageLoading={false}>
            <button type="button">Loaded content</button>
        </SkeletonManager>
    </LazyMotion>
);

describe('SkeletonManager transition', () => {
    beforeEach(() => {
        vi.stubGlobal('matchMedia', () => ({
            matches: false,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        }));
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test('renders ready content underneath the exiting skeleton', async () => {
        const { rerender } = render(renderPage(true));
        expect(screen.queryByRole('button', { name: 'Loaded content' })).not.toBeInTheDocument();

        rerender(renderPage(false));

        expect(screen.getByRole('button', { name: 'Loaded content' })).toBeVisible();
        expect(screen.getByText('Loading placeholder')).toBeInTheDocument();
        expect(screen.getByText('Loading placeholder').parentElement).toHaveAttribute('aria-hidden', 'true');
        await waitFor(() => expect(screen.queryByText('Loading placeholder')).not.toBeInTheDocument());
        expect(screen.getByRole('button', { name: 'Loaded content' })).toBeVisible();
    });

    test('keeps the skeleton when another load starts during its exit', async () => {
        const { rerender } = render(renderPage(true));
        rerender(renderPage(false));
        await waitFor(() => {
            expect(Number(screen.getByText('Loading placeholder').parentElement?.style.opacity)).toBeLessThan(1);
        });

        rerender(renderPage(true));

        await waitFor(() => expect(screen.getByText('Loading placeholder').parentElement).toHaveStyle({ opacity: 1 }));
        expect(screen.queryByRole('button', { name: 'Loaded content' })).not.toBeInTheDocument();

        rerender(renderPage(false));
        await waitFor(() => expect(screen.queryByText('Loading placeholder')).not.toBeInTheDocument());
        expect(screen.getByRole('button', { name: 'Loaded content' })).toBeVisible();
    });

    test('does not introduce a skeleton when content is ready on the initial render', () => {
        render(renderPage(false));

        expect(screen.queryByText('Loading placeholder')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Loaded content' })).toBeVisible();
    });
});
