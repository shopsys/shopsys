import { fireEvent, render, screen } from '@testing-library/react';
import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { onGtmConsentUpdateEventHandlerMock, storeState, updateUserConsentMock } = vi.hoisted(() => ({
    onGtmConsentUpdateEventHandlerMock: vi.fn(),
    storeState: {
        userConsent: {
            marketing: false,
            preferences: false,
            statistics: false,
        },
    },
    updateUserConsentMock: vi.fn(),
}));

vi.mock('gtm/handlers/onGtmConsentUpdateEventHandler', () => ({
    onGtmConsentUpdateEventHandler: onGtmConsentUpdateEventHandlerMock,
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: any) =>
        selector({
            userConsent: storeState.userConsent,
            updateUserConsent: updateUserConsentMock,
        }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string) => key,
    }),
}));

describe('UserConsentForm', () => {
    beforeEach(() => {
        storeState.userConsent = {
            marketing: false,
            preferences: false,
            statistics: false,
        };
    });

    test('sends GTM consent update event with currently selected form values', () => {
        render(<UserConsentForm />);

        fireEvent.click(screen.getByRole('switch', { name: 'Toggle marketing consent' }));
        fireEvent.click(screen.getByRole('button', { name: 'Save choices' }));

        expect(updateUserConsentMock).toHaveBeenCalledWith({
            marketing: true,
            preferences: false,
            statistics: false,
        });
        expect(onGtmConsentUpdateEventHandlerMock).toHaveBeenCalledWith({
            marketing: 'granted',
            preferences: 'denied',
            statistics: 'denied',
        });
    });

    test('renders the compact layout and toggles a preference by clicking its row', () => {
        render(<UserConsentForm layout="compact" />);

        fireEvent.click(screen.getByText('Marketing'));

        expect(screen.getByRole('switch', { name: 'Toggle marketing consent' })).toBeChecked();
    });

    test('does not render the surrounding consent context', () => {
        render(<UserConsentForm />);

        expect(screen.queryByRole('heading', { name: 'User consent' })).not.toBeInTheDocument();
        expect(screen.queryByText(/To learn more, you can read our/)).not.toBeInTheDocument();
    });
});
