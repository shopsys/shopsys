import { act, cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { ComponentProps, ReactNode } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { afterEach, expect, test, vi } from 'vitest';

vi.mock('next/dynamic', () => ({ default: () => () => null }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/ui/useMediaMin', () => ({ useMediaMin: () => false }));
vi.mock('utils/useDeferredRender', () => ({ useDeferredRender: () => true }));
vi.mock('components/Basic/Drawer/Drawer', () => ({
    Drawer: ({
        isActive,
        setIsActive,
        children,
    }: {
        isActive: boolean;
        setIsActive: (value: boolean) => void;
        children: ReactNode;
    }) =>
        isActive ? (
            <section aria-label="Product filters">
                <button type="button" onClick={() => setIsActive(false)}>
                    Close filters
                </button>
                {children}
            </section>
        ) : null,
}));

afterEach(() => {
    cleanup();
    useSessionStore.setState(useSessionStore.getInitialState(), true);
});

test('opens and closes the filter drawer with the real session store', async () => {
    const user = userEvent.setup();
    render(<DeferredFilterPanel {...({} as ComponentProps<typeof DeferredFilterPanel>)} />);

    expect(screen.queryByRole('region', { name: 'Product filters' })).not.toBeInTheDocument();

    act(() => useSessionStore.getState().setIsFilterPanelOpen(true));
    expect(screen.getByRole('region', { name: 'Product filters' })).toBeInTheDocument();

    await user.click(screen.getByRole('button', { name: 'Close filters' }));
    expect(screen.queryByRole('region', { name: 'Product filters' })).not.toBeInTheDocument();
    expect(useSessionStore.getState().isFilterPanelOpen).toBe(false);
});
