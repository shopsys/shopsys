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

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    userConsentPolicyArticleUrl: '/user-consent',
                },
            },
        },
    ],
}));

vi.mock('gtm/handlers/onGtmConsentUpdateEventHandler', () => ({
    onGtmConsentUpdateEventHandler: onGtmConsentUpdateEventHandlerMock,
}));

vi.mock('next-translate/Trans', () => ({
    default: ({ defaultTrans }: { defaultTrans: string }) => <span>{defaultTrans}</span>,
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
});
