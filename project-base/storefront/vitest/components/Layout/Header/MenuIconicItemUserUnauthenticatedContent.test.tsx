import { render, screen } from '@testing-library/react';
import { MenuIconicItemUserUnauthenticatedContent } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent';
import { describe, expect, test, vi } from 'vitest';

const useFocusTrap = vi.hoisted(() => vi.fn());

vi.mock('components/Blocks/Login/LoginForm', () => ({
    LoginForm: () => <form aria-label="Login form" />,
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

vi.mock('utils/useFocusTrap', () => ({ useFocusTrap }));

describe('MenuIconicItemUserUnauthenticatedContent', () => {
    test('starts with login on mobile and leaves focus trapping to its drawer', () => {
        render(
            <MenuIconicItemUserUnauthenticatedContent
                hideFocusTrap
                loginFormName="mobile-login"
                onMenuClose={vi.fn()}
            />,
        );

        expect(screen.getAllByRole('heading').map((heading) => heading.textContent)).toEqual([
            'Log in to your account',
            'Benefits of registration',
        ]);
        expect(useFocusTrap).toHaveBeenCalledWith(undefined);
    });
});
