import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { LoginPopup } from 'components/Blocks/Popup/LoginPopup';
import { LoginPopupLayout } from 'components/Blocks/Popup/LoginPopupLayout';
import { Popup } from 'components/Layout/Popup/Popup';
import type React from 'react';
import { beforeAll, beforeEach, describe, expect, test, vi } from 'vitest';

const { closePortalContentMock, storeCurrentFocusMock, windowDimensionsMock, loginFormMock, exitCompleteMock } =
    vi.hoisted(() => ({
        loginFormMock: vi.fn(),
        exitCompleteMock: vi.fn(),
        closePortalContentMock: vi.fn(),
        storeCurrentFocusMock: vi.fn(),
        windowDimensionsMock: {
            height: 768,
            width: 1024,
        },
    }));

vi.mock('components/Blocks/Login/LoginForm', () => ({
    LoginForm: (props: LoginFormProps) => {
        loginFormMock(props);
        return <form aria-label="Login form" />;
    },
}));

vi.mock('components/Basic/Overlay/Overlay', () => ({
    Overlay: ({ onClick }: { onClick: () => void }) => <button type="button" onClick={onClick} />,
}));

vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children, onExitComplete }: { children: React.ReactNode; onExitComplete: () => void }) => {
        exitCompleteMock(onExitComplete);
        return <>{children}</>;
    },
    m: {
        div: (props: React.ComponentProps<'div'>) => <div {...props} />,
    },
}));

vi.mock('next/dynamic', () => ({
    default:
        () =>
        ({ onClick }: { onClick: () => void }) => <button type="button" onClick={onClick} />,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: any) =>
        selector({
            closePortalContent: closePortalContentMock,
            storeCurrentFocus: storeCurrentFocusMock,
        }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('utils/useFocusTrap', () => ({
    useFocusTrap: vi.fn(),
}));

vi.mock('utils/useKeyPress', () => ({
    useKeypress: vi.fn(),
}));

vi.mock('utils/useWindowDimensions', () => ({
    default: () => windowDimensionsMock,
}));

vi.mock('utils/ui/useMediaMin', () => ({
    useMediaMin: () => windowDimensionsMock.width >= 1024,
}));

describe('Popup accessibility', () => {
    beforeEach(() => {
        windowDimensionsMock.width = 1024;
    });
    beforeAll(() => {
        global.ResizeObserver = class ResizeObserver {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    test('uses title as accessible name and aria description as accessible description', () => {
        render(
            <Popup ariaDescription="Product added to comparison." role="alertdialog" title="Comparison">
                <button type="button">Show products comparison</button>
            </Popup>,
        );

        const popup = screen.getByRole('alertdialog', { name: 'Comparison' });

        expect(popup).toHaveAccessibleDescription('Product added to comparison.');
        expect(popup).toHaveClass('w-[min(50rem,calc(100vw-2.5rem))]');
    });

    test('moves initial focus from the page to the popup title', () => {
        const pageInput = document.createElement('input');
        document.body.append(pageInput);
        pageInput.focus();

        render(
            <Popup role="alertdialog" title="Please check inserted details">
                <button type="button">Close</button>
            </Popup>,
        );

        expect(screen.getByRole('heading', { name: 'Please check inserted details' })).toHaveFocus();

        pageInput.remove();
    });

    test('uses checkout login title as accessible dialog name even when popup title is visually hidden', () => {
        render(<LoginPopup shouldOverwriteCustomerUserCart defaultEmail="customer@example.com" />);

        expect(screen.getByRole('dialog', { name: 'Log in and continue with order' })).toBeInTheDocument();
        expect(loginFormMock).toHaveBeenLastCalledWith(
            expect.objectContaining({
                defaultEmail: 'customer@example.com',
                formName: 'popup-login-form',
                shouldOverwriteCustomerUserCart: true,
            }),
        );
    });

    test('opens mobile checkout login as a named drawer and closes through the portal', async () => {
        windowDimensionsMock.width = 375;
        const user = userEvent.setup();
        render(<LoginPopup shouldOverwriteCustomerUserCart defaultEmail="customer@example.com" />);

        const drawer = await screen.findByRole('dialog', { name: 'Log in and continue with order' });
        expect(drawer).toHaveAttribute('aria-modal', 'true');
        expect(loginFormMock).toHaveBeenLastCalledWith(
            expect.objectContaining({
                defaultEmail: 'customer@example.com',
                shouldOverwriteCustomerUserCart: true,
            }),
        );

        await user.click(screen.getByRole('button', { name: 'Close popup' }));
        expect(closePortalContentMock).not.toHaveBeenCalled();
        act(() => exitCompleteMock.mock.lastCall?.[0]());
        expect(closePortalContentMock).toHaveBeenCalledOnce();
    });

    test('preserves entered credentials when switching between desktop and mobile', async () => {
        const user = userEvent.setup();
        const renderForm = () => (
            <LoginPopupLayout title="Login">
                <input aria-label="Email" defaultValue="original@example.com" />
                <input aria-label="Password" type="password" />
            </LoginPopupLayout>
        );
        const { rerender } = render(renderForm());
        await user.clear(screen.getByRole('textbox', { name: 'Email' }));
        await user.type(screen.getByRole('textbox', { name: 'Email' }), 'draft@example.com');
        await user.type(screen.getByLabelText('Password'), 'unfinished-password');

        windowDimensionsMock.width = 375;
        rerender(renderForm());
        expect(screen.getByRole('textbox', { name: 'Email' })).toHaveValue('draft@example.com');
        expect(screen.getByLabelText('Password')).toHaveValue('unfinished-password');

        windowDimensionsMock.width = 1024;
        rerender(renderForm());
        expect(screen.getByRole('textbox', { name: 'Email' })).toHaveValue('draft@example.com');
        expect(screen.getByLabelText('Password')).toHaveValue('unfinished-password');
    });
});
