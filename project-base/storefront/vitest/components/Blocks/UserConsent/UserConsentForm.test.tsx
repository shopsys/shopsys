import { fireEvent, render, screen } from '@testing-library/react';
import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { renderToString } from 'react-dom/server';
import { UserConsentFormType } from 'types/form';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { onGtmConsentUpdateEventHandlerMock, storeState, updateUserConsentMock } = vi.hoisted(() => ({
    onGtmConsentUpdateEventHandlerMock: vi.fn(),
    storeState: {
        userConsent: null as UserConsentFormType | null,
    },
    updateUserConsentMock: vi.fn(),
}));

vi.mock('gtm/handlers/onGtmConsentUpdateEventHandler', () => ({
    onGtmConsentUpdateEventHandler: onGtmConsentUpdateEventHandlerMock,
}));

vi.mock('store/usePersistStore', () => {
    const store = { getState: () => storeState };

    return {
        usePersistStoreApi: () => store,
        usePersistStore: (selector: any) => selector({ ...storeState, updateUserConsent: updateUserConsentMock }),
    };
});

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string) => key,
    }),
}));

describe('UserConsentForm', () => {
    beforeEach(() => {
        storeState.userConsent = null;
    });

    test('hydrates the server-rendered form with saved choices', () => {
        const container = document.body.appendChild(document.createElement('div'));
        container.innerHTML = renderToString(<UserConsentForm />);

        storeState.userConsent = { marketing: true, preferences: false, statistics: true };
        render(<UserConsentForm />, { container, hydrate: true });

        expect(screen.getByRole('switch', { name: 'Toggle marketing consent' })).toBeChecked();
        expect(screen.getByRole('switch', { name: 'Toggle statistics consent' })).toBeChecked();
        expect(screen.getByRole('switch', { name: 'Toggle preferences consent' })).not.toBeChecked();

        fireEvent.click(screen.getByRole('button', { name: 'Save choices' }));

        expect(updateUserConsentMock).toHaveBeenCalledWith(storeState.userConsent);
    });

    test.each([null, { marketing: false, preferences: false, statistics: false }])(
        'keeps unsaved choices when another tab updates consent (initial consent: %j)',
        (initialConsent) => {
            storeState.userConsent = initialConsent;
            const { rerender } = render(<UserConsentForm />);

            fireEvent.click(screen.getByRole('switch', { name: 'Toggle marketing consent' }));
            storeState.userConsent = { marketing: false, preferences: true, statistics: true };
            rerender(<UserConsentForm />);

            expect(screen.getByRole('switch', { name: 'Toggle marketing consent' })).toBeChecked();
            expect(screen.getByRole('switch', { name: 'Toggle preferences consent' })).not.toBeChecked();
            expect(screen.getByRole('switch', { name: 'Toggle statistics consent' })).not.toBeChecked();

            fireEvent.click(screen.getByRole('button', { name: 'Save choices' }));

            expect(updateUserConsentMock).toHaveBeenCalledWith({
                marketing: true,
                preferences: false,
                statistics: false,
            });
        },
    );

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
