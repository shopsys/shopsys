import { render, screen } from '@testing-library/react';
import { RegistrationUser } from 'components/Pages/Registration/RegistrationUser';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Forms/Form/Form', () => ({
    FormBlockWrapper: ({ children }: React.PropsWithChildren) => <div>{children}</div>,
    FormHeading: ({ children }: React.PropsWithChildren) => <h2>{children}</h2>,
}));

vi.mock('components/Forms/Lib/FormColumn', () => ({
    FormColumn: ({ children }: React.PropsWithChildren) => <div>{children}</div>,
}));

vi.mock('components/Forms/PhonePrefixSelect/PhoneNumberInputControlled', () => ({
    PhoneNumberInputControlled: () => null,
}));

vi.mock('components/Forms/TextInput/TextInputControlled', () => ({
    TextInputControlled: ({ name, textInputProps }: any) => {
        const { label, ...inputProps } = textInputProps;

        return <input {...inputProps} aria-label={label} name={name} />;
    },
}));

vi.mock('components/Pages/Registration/registrationFormMeta', () => ({
    useRegistrationFormMeta: () => ({
        formName: 'registration-form',
        fields: {
            email: { label: 'Email', name: 'email' },
            firstName: { label: 'First name', name: 'firstName' },
            lastName: { label: 'Last name', name: 'lastName' },
            telephone: { label: 'Phone', name: 'telephone' },
            telephonePrefix: { name: 'telephonePrefix' },
            telephonePrefixCountryCode: { name: 'telephonePrefixCountryCode' },
        },
    }),
}));

vi.mock('react-hook-form', () => ({
    useFormContext: () => ({ control: {} }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('RegistrationUser', () => {
    test('does not reference a missing email description', () => {
        render(<RegistrationUser />);

        expect(screen.getByRole('textbox', { name: 'Email' })).not.toHaveAttribute('aria-describedby');
    });
});
