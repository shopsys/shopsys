import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { useState } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: any) => children,
    m: {
        div: ({ children, ...props }: any) => <div {...props}>{children}</div>,
    },
}));

vi.mock('utils/ui/useMediaMin', () => ({
    useMediaMin: () => false,
}));

const DrawerTestWrapper: FC = () => {
    const [isActive, setIsActive] = useState(false);

    return (
        <>
            <button type="button" onClick={() => setIsActive(true)}>
                Open cart
            </button>

            <Drawer isActive={isActive} setIsActive={setIsActive} title="Cart">
                <a href="/cart">Cart link</a>
                <button type="button">Checkout</button>
            </Drawer>
        </>
    );
};

describe('Drawer', () => {
    test('opens as modal dialog, traps focus, and restores focus after close', async () => {
        const user = userEvent.setup();

        render(<DrawerTestWrapper />);

        const openButton = screen.getByRole('button', { name: 'Open cart' });
        await user.click(openButton);

        const dialog = await screen.findByRole('dialog', { name: 'Cart' });
        expect(dialog).toHaveAttribute('aria-modal', 'true');

        const closeButton = screen.getByTitle('Close');
        await waitFor(() => expect(closeButton).toHaveFocus());

        await user.tab();
        expect(screen.getByRole('link', { name: 'Cart link' })).toHaveFocus();

        await user.tab();
        expect(screen.getByRole('button', { name: 'Checkout' })).toHaveFocus();

        await user.tab();
        expect(closeButton).toHaveFocus();

        await user.keyboard('{Escape}');

        await waitFor(() => expect(screen.queryByRole('dialog', { name: 'Cart' })).not.toBeInTheDocument());
        expect(openButton).toHaveFocus();
    });
});
