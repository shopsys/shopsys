import { cleanup, render, screen } from '@testing-library/react';
import { CartInHeaderList } from 'components/Layout/Header/Cart/CartInHeaderList';
import { afterEach, beforeAll, describe, expect, test, vi } from 'vitest';

const useFocusTrap = vi.hoisted(() => vi.fn());

vi.mock('components/Blocks/FreeTransport/FreeTransportRange', () => ({
    FreeTransportRange: () => <div>Free transport</div>,
}));

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({ children }: React.PropsWithChildren) => <a href="/cart">{children}</a>,
}));

vi.mock('components/Layout/Header/Cart/CartInHeaderListItem', () => ({
    CartInHeaderListItem: () => <li>Cart item</li>,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: { items: [{ uuid: 'cart-item' }] } }),
}));

vi.mock('utils/cart/useRemoveFromCart', () => ({
    useRemoveFromCart: () => ({ isRemovingFromCart: false, removeFromCart: vi.fn() }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/cart'],
}));

vi.mock('utils/useFocusTrap', () => ({ useFocusTrap }));

describe('CartInHeaderList', () => {
    beforeAll(() => {
        global.ResizeObserver = class ResizeObserver {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    afterEach(() => {
        cleanup();
        useFocusTrap.mockClear();
    });

    test('keeps the desktop popover capped and focus-trapped', () => {
        render(<CartInHeaderList />);
        const scrollContainer = screen.getByRole('list').parentElement;

        expect(scrollContainer).toHaveClass('max-h-[50dvh]');
        expect(useFocusTrap.mock.calls[0]?.[0]).toHaveProperty('current');
    });

    test('fills a drawer while leaving focus trapping to the drawer', () => {
        render(<CartInHeaderList hideFocusTrap isDrawer />);
        const scrollContainer = screen.getByRole('list').parentElement;

        expect(scrollContainer).toHaveClass('min-h-0', 'flex-1');
        expect(scrollContainer?.parentElement).toHaveClass('flex', 'min-h-0', 'flex-1', 'flex-col');
        expect(useFocusTrap).toHaveBeenCalledWith(undefined);
    });
});
