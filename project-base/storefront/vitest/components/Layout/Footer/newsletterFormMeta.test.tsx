import { render, screen } from '@testing-library/react';
import { useNewsletterSubscriptionAgreement } from 'components/Layout/Footer/NewsletterForm/newsletterFormMeta';
import type { AnchorHTMLAttributes, ReactElement } from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { settingsState } = vi.hoisted(() => ({
    settingsState: {
        privacyPolicyArticleUrl: undefined as string | undefined,
    },
}));

vi.mock('components/Basic/Link/Link', () => ({
    Link: ({ children, ...props }: AnchorHTMLAttributes<HTMLAnchorElement>) => <a {...props}>{children}</a>,
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    privacyPolicyArticleUrl: settingsState.privacyPolicyArticleUrl,
                },
            },
        },
    ],
}));

vi.mock('next-translate/Trans', async () => {
    const { cloneElement } = await import('react');

    return {
        default: ({ components }: { components: { lnk1: ReactElement } }) =>
            cloneElement(components.lnk1, {}, 'Personal data processing'),
    };
});

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

const NewsletterAgreement = () => <p>{useNewsletterSubscriptionAgreement()}</p>;

describe('useNewsletterSubscriptionAgreement', () => {
    beforeEach(() => {
        settingsState.privacyPolicyArticleUrl = undefined;
    });

    test('renders unavailable privacy policy as ordinary text', () => {
        render(<NewsletterAgreement />);

        const agreementText = screen.getByText('Personal data processing');
        expect(agreementText).not.toHaveAttribute('href');
    });

    test('renders an available privacy policy as a link', () => {
        settingsState.privacyPolicyArticleUrl = '/privacy-policy';

        render(<NewsletterAgreement />);

        const agreementLink = screen.getByRole('link', { name: 'Go to privacy policy article' });
        expect(agreementLink).toHaveAttribute('href', '/privacy-policy');
    });
});
