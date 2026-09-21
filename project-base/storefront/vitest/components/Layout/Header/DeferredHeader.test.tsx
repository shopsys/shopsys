import { render, screen } from '@testing-library/react';
import { DeferredAutocompleteSearch } from 'components/Layout/Header/AutocompleteSearch/DeferredAutocompleteSearch';
import { DeferredCartInHeader } from 'components/Layout/Header/Cart/DeferredCartInHeader';
import { DeferredMenuIconic } from 'components/Layout/Header/MenuIconic/DeferredMenuIconic';
import { describe, expect, test, vi } from 'vitest';

const { deferredReady } = vi.hoisted(() => ({ deferredReady: vi.fn(() => true) }));

vi.mock('utils/useDeferredRender', () => ({ useDeferredRender: deferredReady }));
vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: true }),
}));
vi.mock('components/Blocks/Skeleton/SkeletonModuleAutocompleteSearch', () => ({
    SkeletonModuleAutocompleteSearch: () => <div>Search placeholder</div>,
}));
vi.mock('components/Blocks/Skeleton/SkeletonModuleCartInHeader', () => ({
    SkeletonModuleCartInHeader: () => <div>Cart placeholder</div>,
}));
vi.mock('components/Layout/Header/MenuIconic/MenuIconicPlaceholder', () => ({
    MenuIconicPlaceholder: () => <div>Menu placeholder</div>,
}));
vi.mock('components/Layout/Header/AutocompleteSearch/AutocompleteSearch', () => ({
    AutocompleteSearch: () => <div>Interactive search</div>,
}));
vi.mock('components/Layout/Header/Cart/CartInHeader', () => ({
    CartInHeader: () => <div>Interactive cart</div>,
}));
vi.mock('components/Layout/Header/MenuIconic/MenuIconic', () => ({
    MenuIconic: () => <div>Interactive menu</div>,
}));

const HeaderParts = ({ isDesktop }: { isDesktop: boolean | undefined }) => (
    <>
        <DeferredAutocompleteSearch isDesktop={isDesktop} />
        <DeferredCartInHeader isDesktop={isDesktop} />
        <DeferredMenuIconic isDesktop={isDesktop} />
    </>
);

describe('deferred desktop header', () => {
    test.each([undefined, false])('keeps placeholders when desktop is %s even after the deferred wave', (isDesktop) => {
        render(<HeaderParts isDesktop={isDesktop} />);

        expect(screen.getByText('Search placeholder')).toBeInTheDocument();
        expect(screen.getByText('Cart placeholder')).toBeInTheDocument();
        expect(screen.getByText('Menu placeholder')).toBeInTheDocument();
        expect(screen.queryByText('Interactive search')).not.toBeInTheDocument();
        expect(screen.queryByText('Interactive cart')).not.toBeInTheDocument();
        expect(screen.queryByText('Interactive menu')).not.toBeInTheDocument();
    });

    test('still waits for the deferred wave on desktop', () => {
        deferredReady.mockReturnValueOnce(false).mockReturnValueOnce(false).mockReturnValueOnce(false);

        render(<HeaderParts isDesktop />);

        expect(screen.getByText('Search placeholder')).toBeInTheDocument();
        expect(screen.getByText('Cart placeholder')).toBeInTheDocument();
        expect(screen.getByText('Menu placeholder')).toBeInTheDocument();
    });

    test('mounts after switching to desktop and unmounts after switching back to mobile', async () => {
        const { rerender } = render(<HeaderParts isDesktop={false} />);

        rerender(<HeaderParts isDesktop />);

        expect(await screen.findByText('Interactive search')).toBeInTheDocument();
        expect(await screen.findByText('Interactive cart')).toBeInTheDocument();
        expect(await screen.findByText('Interactive menu')).toBeInTheDocument();

        rerender(<HeaderParts isDesktop={false} />);

        expect(screen.queryByText('Interactive search')).not.toBeInTheDocument();
        expect(screen.queryByText('Interactive cart')).not.toBeInTheDocument();
        expect(screen.queryByText('Interactive menu')).not.toBeInTheDocument();
    });
});
