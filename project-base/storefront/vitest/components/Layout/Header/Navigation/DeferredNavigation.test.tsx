import { render, screen } from '@testing-library/react';
import { DeferredNavigation } from 'components/Layout/Header/Navigation/DeferredNavigation';
import type { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { navigationModulePromise, navigationRenderMock, resolveNavigationModule, useDeferredRenderMock } = vi.hoisted(
    () => {
        let resolveNavigationModulePromise: () => void;
        const navigationModulePromise = new Promise<void>((resolve) => {
            resolveNavigationModulePromise = resolve;
        });

        return {
            navigationModulePromise,
            navigationRenderMock: vi.fn(),
            resolveNavigationModule: () => resolveNavigationModulePromise(),
            useDeferredRenderMock: vi.fn(),
        };
    },
);

vi.mock('utils/useDeferredRender', () => ({
    useDeferredRender: useDeferredRenderMock,
}));

vi.mock('components/Layout/Header/Navigation/NavigationPlaceholder', () => ({
    NavigationPlaceholder: ({ navigation }: { navigation?: Array<{ name: string }> }) => (
        <div>{navigation?.[0]?.name ?? 'Navigation skeleton'}</div>
    ),
}));

vi.mock('components/Layout/Header/Navigation/Navigation', async () => {
    await navigationModulePromise;

    return {
        Navigation: () => {
            navigationRenderMock();

            return <div>Desktop navigation</div>;
        },
    };
});

const navigation = [{ name: 'Catalog' } as TypeCategoriesByColumnFragment];

describe('DeferredNavigation', () => {
    beforeEach(() => {
        navigationRenderMock.mockClear();
        useDeferredRenderMock.mockReset();
        useDeferredRenderMock.mockReturnValue(true);
    });

    test('keeps navigation unmounted on mobile after the deferred wave', () => {
        render(<DeferredNavigation isDesktop={false} navigation={navigation} />);

        expect(screen.queryByText('Catalog')).not.toBeInTheDocument();
        expect(screen.queryByText('Desktop navigation')).not.toBeInTheDocument();
        expect(navigationRenderMock).not.toHaveBeenCalled();
        expect(useDeferredRenderMock).not.toHaveBeenCalled();
    });

    test('renders the placeholder before the viewport is recognized', () => {
        render(<DeferredNavigation navigation={navigation} />);

        expect(screen.getByText('Catalog')).toBeInTheDocument();
        expect(useDeferredRenderMock).not.toHaveBeenCalled();
    });

    test('keeps the placeholder on desktop before the deferred wave', () => {
        useDeferredRenderMock.mockReturnValue(false);

        render(<DeferredNavigation isDesktop navigation={navigation} />);

        expect(screen.getByText('Catalog')).toBeInTheDocument();
        expect(navigationRenderMock).not.toHaveBeenCalled();
    });

    test('keeps navigation labels visible while loading the interactive navigation', async () => {
        render(<DeferredNavigation isDesktop navigation={navigation} />);

        expect(screen.getByText('Catalog')).toBeInTheDocument();
        expect(screen.queryByText('Navigation skeleton')).not.toBeInTheDocument();
        expect(screen.queryByText('Desktop navigation')).not.toBeInTheDocument();

        resolveNavigationModule();

        expect(await screen.findByText('Desktop navigation')).toBeInTheDocument();
        expect(navigationRenderMock).toHaveBeenCalledOnce();
    });
});
