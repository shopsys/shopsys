import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MenuIconicItemUserUnauthenticatedContent } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Image/Image', () => ({ Image: () => null }));

vi.mock('components/Blocks/Login/LoginForm', () => ({
    LoginForm: () => <form aria-label="Login form" />,
}));

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href, onClick }: React.PropsWithChildren<{ href: string; onClick: () => void }>) => (
        <a
            href={href}
            onClick={(event) => {
                event.preventDefault();
                onClick();
            }}
        >
            {children}
        </a>
    ),
}));

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({ children }: React.PropsWithChildren) => <a href="/registration">{children}</a>,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/registration'],
}));

describe('MenuIconicItemUserUnauthenticatedContent', () => {
    test('offers registration after the form and closes its container', async () => {
        const onMenuClose = vi.fn();
        const user = userEvent.setup();
        render(
            <MenuIconicItemUserUnauthenticatedContent loginFormName="registration-entry" onMenuClose={onMenuClose} />,
        );

        const prompt = screen.getByText("Don't have an account?");
        const registrationLink = within(prompt).getByRole('link', { name: 'Register' });
        expect(registrationLink).toHaveAttribute('href', '/registration');
        expect(
            prompt.compareDocumentPosition(screen.getByRole('form', { name: 'Login form' })) &
                Node.DOCUMENT_POSITION_PRECEDING,
        ).toBeTruthy();
        await user.click(registrationLink);
        expect(onMenuClose).toHaveBeenCalledOnce();
    });

    test('keeps login before registration benefits in reading order', () => {
        render(<MenuIconicItemUserUnauthenticatedContent loginFormName="mobile-login" onMenuClose={vi.fn()} />);

        expect(screen.getAllByRole('heading').map((heading) => heading.textContent)).toEqual([
            'Login',
            'Why create an account?',
        ]);
    });
});
