import { render } from '@testing-library/react';
import { CartStickyBar } from 'components/Pages/Cart/CartStickyBar';
import { createRef } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({
        cart: {
            items: Array.from({ length: 5 }, (_, index) => ({ uuid: `item-${index}` })),
            totalItemsPrice: { priceWithVat: '100' },
        },
    }),
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string) => price,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/ui/useIntersectionObserver', () => ({
    useIntersectionObserver: () => ({ isIntersecting: false }),
}));

vi.mock('components/Pages/Cart/cartUtils', () => ({
    useCartPageNavigation: () => ({ goToNextStepFromCartPage: vi.fn() }),
}));

describe('CartStickyBar', () => {
    test('participates in the mobile bottom layer and remains fixed on desktop', () => {
        const { container } = render(<CartStickyBar originalButtonRef={createRef<HTMLDivElement>()} />);

        expect(container.firstElementChild).not.toHaveClass('fixed');
        expect(container.firstElementChild).toHaveClass(
            'pointer-events-auto',
            'translate-y-0',
            'vl:fixed',
            'vl:bottom-0',
            'vl:z-floatingAction',
        );
    });
});
