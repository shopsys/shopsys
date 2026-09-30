import { render, screen } from '@testing-library/react';
import { CartContent } from 'components/Pages/Cart/CartContent';
import { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Adverts/Adverts', () => ({
    Adverts: ({ positionName }: { positionName: string }) => <div data-testid="adverts">{positionName}</div>,
}));

vi.mock('components/Blocks/CartSteps/CartSteps', () => ({
    CartSteps: () => <div>Cart steps</div>,
}));

vi.mock('components/Layout/VerticalStack/VerticalStack', () => ({
    VerticalStack: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Pages/Cart/CartList/CartList', () => ({
    CartList: () => <div>Cart list</div>,
}));

vi.mock('components/Pages/Cart/CartSummary', () => ({
    CartSummary: () => <div>Cart summary</div>,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ isLuigisBoxActive: false, url: 'https://example.com' }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('CartContent', () => {
    test('renders cart preview adverts above the cart summary', () => {
        render(<CartContent cart={{ items: [] } as any} cartPreviewRef={{ current: null }} />);

        const adverts = screen.getByTestId('adverts');
        const cartSummary = screen.getByText('Cart summary');

        expect(adverts).toHaveTextContent('cartPreview');
        expect(adverts.compareDocumentPosition(cartSummary)).toBe(Node.DOCUMENT_POSITION_FOLLOWING);
    });
});
