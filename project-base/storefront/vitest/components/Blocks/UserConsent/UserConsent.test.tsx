import { render, screen, within } from '@testing-library/react';
import { UserConsent } from 'components/Blocks/UserConsent/UserConsent';
import { ComponentProps, cloneElement, ReactElement } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, ...props }: ComponentProps<'a'>) => <a {...props}>{children}</a>,
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    userConsentPolicyArticleUrl: '/user-consent-policy',
                },
            },
        },
    ],
}));

vi.mock('gtm/handlers/onGtmConsentUpdateEventHandler', () => ({
    onGtmConsentUpdateEventHandler: vi.fn(),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ asPath: '/' }),
}));

vi.mock('next-translate/Trans', () => ({
    default: ({ components }: { components: { link: ReactElement<ComponentProps<'a'>> } }) => (
        <>
            To learn more, you can read our {cloneElement(components.link, { children: 'consent and tracking policy' })}
        </>
    ),
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: any) =>
        selector({
            updateUserConsent: vi.fn(),
            userConsent: null,
        }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string) => key,
    }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/user-consent'],
}));

describe('UserConsent', () => {
    test('renders the floating context around the compact form', () => {
        render(<UserConsent url="https://example.com" />);
        const floatingConsent = screen.getByRole('complementary', { name: 'User consent' });

        expect(within(floatingConsent).getByRole('heading', { name: 'User consent' })).toBeInTheDocument();
        expect(within(floatingConsent).getByRole('link', { name: 'consent and tracking policy' })).toBeInTheDocument();
        expect(within(floatingConsent).getByRole('switch', { name: 'Toggle marketing consent' })).toBeInTheDocument();
    });
});
