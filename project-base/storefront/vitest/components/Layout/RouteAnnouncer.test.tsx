import { act, render, screen } from '@testing-library/react';
import { RouteAnnouncer } from 'components/Layout/RouteAnnouncer';
import { useSessionStore } from 'store/useSessionStore';
import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest';

type EventCallback = (...args: any[]) => void;

const createRouterEvents = () => {
    const listeners = new Map<string, Set<EventCallback>>();

    return {
        on: (event: string, cb: EventCallback) => {
            if (!listeners.has(event)) {
                listeners.set(event, new Set());
            }
            listeners.get(event)!.add(cb);
        },
        off: (event: string, cb: EventCallback) => {
            listeners.get(event)?.delete(cb);
        },
        emit: (event: string, ...payload: any[]) => {
            listeners.get(event)?.forEach((cb) => cb(...payload));
        },
        clearAll: () => listeners.clear(),
    };
};

const routerEvents = createRouterEvents();

vi.mock('next/router', () => ({
    useRouter: () => ({
        events: routerEvents,
    }),
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string, params?: { pageTitle: string }) => {
            if (key === 'Page loading') {
                return 'Page loading';
            }

            if (params?.pageTitle) {
                return `You are on ${params.pageTitle} page`;
            }

            return key;
        },
    }),
}));

const resetSessionStore = () => {
    act(() => {
        useSessionStore.setState({
            hadClientSideNavigation: false,
            isPageLoading: false,
        });
    });
};

const advanceTimersBy = (ms: number) => {
    act(() => {
        vi.advanceTimersByTime(ms);
    });
};

const waitForAnnouncement = (expected: string, stepMs = 80, maxIterations = 120) => {
    let lastText = '';
    for (let iteration = 0; iteration < maxIterations; iteration += 1) {
        lastText = screen.getByRole('status').textContent || '';
        if (lastText.includes(expected)) {
            return lastText;
        }

        advanceTimersBy(stepMs);
    }

    return lastText;
};

describe('RouteAnnouncer', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        resetSessionStore();
        routerEvents.clearAll();
        document.title = 'Initial Page Title';
    });

    afterEach(() => {
        act(() => {
            vi.runOnlyPendingTimers();
        });
        vi.useRealTimers();
        resetSessionStore();
    });

    const renderAnnouncer = () => {
        let utils: ReturnType<typeof render> | undefined;
        act(() => {
            utils = render(<RouteAnnouncer />);
        });
        return utils!;
    };

    test('announces server-rendered title after hydration', () => {
        renderAnnouncer();

        advanceTimersBy(60);

        expect(screen.getByRole('status')).toHaveTextContent('You are on Initial Page Title page');
    });

    test('suppresses announcements during loading state', () => {
        renderAnnouncer();
        advanceTimersBy(60);

        expect(screen.getByRole('status')).toHaveTextContent('You are on Initial Page Title page');

        act(() => {
            routerEvents.emit('routeChangeStart', '/next', { shallow: false });
        });

        act(() => {
            useSessionStore.setState({ hadClientSideNavigation: true, isPageLoading: true });
        });

        const loadingMessage = waitForAnnouncement('Page loading');

        expect(loadingMessage).toBe('Page loading');
        expect(document.title).toBe('Page loading');

        advanceTimersBy(500);

        expect(screen.getByRole('status')).toHaveTextContent('Page loading');
    });

    test('announces new page title once loading completes', () => {
        renderAnnouncer();
        advanceTimersBy(60);

        act(() => {
            routerEvents.emit('routeChangeStart', '/next', { shallow: false });
        });

        act(() => {
            useSessionStore.setState({ hadClientSideNavigation: true, isPageLoading: true });
        });
        const loadingMessage = waitForAnnouncement('Page loading');
        expect(loadingMessage).toBe('Page loading');
        expect(document.title).toBe('Page loading');

        act(() => {
            useSessionStore.setState({ isPageLoading: false });
        });

        advanceTimersBy(80);

        document.title = 'Product Detail - New Title';

        const finalMessage = waitForAnnouncement('Product Detail - New Title');

        expect(finalMessage).toContain('You are on Product Detail - New Title page');
    });
});
