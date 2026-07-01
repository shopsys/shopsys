import { render, screen } from '@testing-library/react';
import { LoginPopup } from 'components/Blocks/Popup/LoginPopup';
import { Popup } from 'components/Layout/Popup/Popup';
import type React from 'react';
import { beforeAll, describe, expect, test, vi } from 'vitest';

const { closePortalContentMock, storeCurrentFocusMock, windowDimensionsMock } = vi.hoisted(() => ({
    closePortalContentMock: vi.fn(),
    storeCurrentFocusMock: vi.fn(),
    windowDimensionsMock: {
        height: 768,
        width: 1024,
    },
}));

vi.mock('components/Blocks/Login/LoginForm', () => ({
    LoginForm: () => <form aria-label="Login form" />,
}));

vi.mock('components/Basic/Overlay/Overlay', () => ({
    Overlay: ({ onClick }: { onClick: () => void }) => <button type="button" onClick={onClick} />,
}));

vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: { children: React.ReactNode }) => <>{children}</>,
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

describe('Popup accessibility', () => {
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
    });

    test('uses checkout login title as accessible dialog name even when popup title is visually hidden', () => {
        render(<LoginPopup shouldOverwriteCustomerUserCart defaultEmail="customer@example.com" />);

        expect(screen.getByRole('dialog', { name: 'Log in and continue with order' })).toBeInTheDocument();
    });
});
