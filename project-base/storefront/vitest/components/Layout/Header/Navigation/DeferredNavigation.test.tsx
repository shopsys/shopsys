import { render, screen } from '@testing-library/react';
import { DeferredNavigation } from 'components/Layout/Header/Navigation/DeferredNavigation';
import type { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { navigationRenderMock, useDeferredRenderMock } = vi.hoisted(() => ({
    navigationRenderMock: vi.fn(),
    useDeferredRenderMock: vi.fn(),
}));

vi.mock('utils/useDeferredRender', () => ({
    useDeferredRender: useDeferredRenderMock,
}));

vi.mock('components/Layout/Header/Navigation/NavigationPlaceholder', () => ({
    NavigationPlaceholder: () => <div>Navigation placeholder</div>,
}));

vi.mock('components/Layout/Header/Navigation/Navigation', () => ({
    Navigation: () => {
        navigationRenderMock();

        return <div>Desktop navigation</div>;
    },
}));

const navigation = [{} as TypeCategoriesByColumnFragment];

describe('DeferredNavigation', () => {
    beforeEach(() => {
        navigationRenderMock.mockClear();
        useDeferredRenderMock.mockReset();
        useDeferredRenderMock.mockReturnValue(true);
    });

    test('keeps navigation unmounted on mobile after the deferred wave', () => {
        render(<DeferredNavigation isDesktop={false} navigation={navigation} />);

        expect(screen.queryByText('Navigation placeholder')).not.toBeInTheDocument();
        expect(screen.queryByText('Desktop navigation')).not.toBeInTheDocument();
        expect(navigationRenderMock).not.toHaveBeenCalled();
        expect(useDeferredRenderMock).not.toHaveBeenCalled();
    });

    test('renders the placeholder before the viewport is recognized', () => {
        render(<DeferredNavigation navigation={navigation} />);

        expect(screen.getByText('Navigation placeholder')).toBeInTheDocument();
        expect(useDeferredRenderMock).not.toHaveBeenCalled();
    });

    test('keeps the placeholder on desktop before the deferred wave', () => {
        useDeferredRenderMock.mockReturnValue(false);

        render(<DeferredNavigation isDesktop navigation={navigation} />);

        expect(screen.getByText('Navigation placeholder')).toBeInTheDocument();
        expect(navigationRenderMock).not.toHaveBeenCalled();
    });

    test('mounts desktop navigation after the deferred wave', async () => {
        render(<DeferredNavigation isDesktop navigation={navigation} />);

        expect(await screen.findByText('Desktop navigation')).toBeInTheDocument();
        expect(navigationRenderMock).toHaveBeenCalledOnce();
    });
});
