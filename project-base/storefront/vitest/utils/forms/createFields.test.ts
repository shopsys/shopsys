import { createElement } from 'react';
import { createFields } from 'utils/forms/createFields';
import { describe, expect, test } from 'vitest';

type SimpleForm = {
    email: string;
    password: string;
};

type ExtendedForm = {
    email: string;
    telephone: string;
};

describe('createFields', () => {
    test('generates { name, label } for string labels', () => {
        const fields = createFields<SimpleForm>({
            email: 'Your email',
            password: 'Your password',
        });

        expect(fields.email).toEqual({ name: 'email', label: 'Your email' });
        expect(fields.password).toEqual({ name: 'password', label: 'Your password' });
    });

    test('generates { name, label } for ReactElement labels', () => {
        const emailLabel = createElement('span', null, 'Email');
        const passwordLabel = createElement('span', null, 'Password');

        const fields = createFields<SimpleForm>({
            email: emailLabel,
            password: passwordLabel,
        });

        expect(fields.email).toEqual({ name: 'email', label: emailLabel });
        expect(fields.password).toEqual({ name: 'password', label: passwordLabel });
    });

    test('includes extras for extended fields', () => {
        const fields = createFields<ExtendedForm, { disabled?: boolean }>({
            email: { label: 'Your email', disabled: true },
            telephone: { label: 'Phone', disabled: false },
        });

        expect(fields.email).toEqual({ name: 'email', label: 'Your email', disabled: true });
        expect(fields.telephone).toEqual({ name: 'telephone', label: 'Phone', disabled: false });
    });

    test('handles mixed simple and extended fields', () => {
        const fields = createFields<ExtendedForm, { disabled?: boolean }>({
            email: { label: 'Your email', disabled: true },
            telephone: 'Phone',
        });

        expect(fields.email).toEqual({ name: 'email', label: 'Your email', disabled: true });
        expect(fields.telephone).toEqual({ name: 'telephone', label: 'Phone' });
    });
});
