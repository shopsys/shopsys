import { act, cleanup, renderHook } from '@testing-library/react';
import { StrictMode } from 'react';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';
import { useDeferredRender } from 'utils/useDeferredRender';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const state = vi.hoisted(() => ({ pathname: '/', hadClientSideNavigation: false }));
vi.mock('envConfig', () => ({ getPublicConfigProperty: () => true }));
vi.mock('next/router', () => ({ useRouter: () => ({ pathname: state.pathname }) }));
vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (store: { hadClientSideNavigation: boolean }) => boolean) => selector(state),
}));

describe('deferred render readiness', () => {
    beforeEach(() => {
        state.pathname = '/';
        state.hadClientSideNavigation = false;
        vi.useFakeTimers();
    });

    afterEach(() => {
        cleanup();
        vi.useRealTimers();
        expect(document.body).not.toHaveAttribute('data-deferred-render-pending');
    });

    test('stays pending until every deferred wave has committed', async () => {
        const footer = renderHook(() => useDeferredRender('footer'));
        const newsletter = renderHook(() => useDeferredRender('newsletter'));
        expect(document.body).toHaveAttribute('data-deferred-render-pending', '2');
        await act(() => vi.advanceTimersByTimeAsync(300));
        expect(footer.result.current).toBe(true);
        expect(newsletter.result.current).toBe(false);
        expect(document.body).toHaveAttribute('data-deferred-render-pending', '1');
        await act(() => vi.advanceTimersByTimeAsync(600));
        expect(newsletter.result.current).toBe(true);
        expect(document.body).not.toHaveAttribute('data-deferred-render-pending');
    });

    test('cancels pending work on unmount, including Strict Mode effect replay', () => {
        const { result, unmount } = renderHook(() => useDeferredRender('newsletter'), { wrapper: StrictMode });
        expect(result.current).toBe(false);
        expect(document.body).toHaveAttribute('data-deferred-render-pending', '1');
        unmount();
        expect(document.body).not.toHaveAttribute('data-deferred-render-pending');
        expect(vi.getTimerCount()).toBe(0);
    });

    test('does not delay client-side navigation or non-deferred pages', () => {
        state.hadClientSideNavigation = true;
        expect(renderHook(() => useDeferredRender('footer')).result.current).toBe(true);
        state.hadClientSideNavigation = false;
        state.pathname = '/cart';
        expect(renderHook(() => useDeferredRender('footer')).result.current).toBe(true);
        expect(document.body).not.toHaveAttribute('data-deferred-render-pending');
        expect(vi.getTimerCount()).toBe(0);
    });

    test('keeps the product page final wave pending for its full delay', async () => {
        state.pathname = FriendlyPagesDestinations.product;
        const { result } = renderHook(() => useDeferredRender('newsletter'));
        await act(() => vi.advanceTimersByTimeAsync(900));
        expect(result.current).toBe(false);
        expect(document.body).toHaveAttribute('data-deferred-render-pending', '1');
        await act(() => vi.advanceTimersByTimeAsync(100));
        expect(result.current).toBe(true);
        expect(document.body).not.toHaveAttribute('data-deferred-render-pending');
    });
});
