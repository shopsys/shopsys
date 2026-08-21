import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { PromoCode } from 'components/Blocks/PromoCode/PromoCode';
import { ComponentProps, useState } from 'react';
import { describe, expect, test, vi } from 'vitest';

const MockPromoCodeForm = ({ isContentVisible }: { isContentVisible: boolean }) => {
    const [promoCode, setPromoCode] = useState('');

    return (
        <input
            aria-label="Promo code"
            hidden={!isContentVisible}
            value={promoCode}
            onChange={(event) => setPromoCode(event.target.value)}
        />
    );
};

vi.mock('next/dynamic', () => ({
    default: () => (props: ComponentProps<typeof MockPromoCodeForm>) => <MockPromoCodeForm {...props} />,
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ promoCodes: [] }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('PromoCode', () => {
    test('keeps the entered promo code when the form is hidden and shown again', async () => {
        const user = userEvent.setup();

        render(<PromoCode />);

        const toggle = screen.getByRole('checkbox', { name: 'I have a discount coupon' });
        expect(screen.queryByRole('textbox', { name: 'Promo code' })).not.toBeInTheDocument();

        await user.click(toggle);

        const promoCodeInput = screen.getByRole('textbox', { name: 'Promo code' });
        await user.type(promoCodeInput, 'SAVE10');

        await user.click(toggle);
        expect(screen.queryByRole('textbox', { name: 'Promo code' })).not.toBeInTheDocument();

        await user.click(toggle);
        expect(screen.getByRole('textbox', { name: 'Promo code' })).toHaveValue('SAVE10');
    });
});
